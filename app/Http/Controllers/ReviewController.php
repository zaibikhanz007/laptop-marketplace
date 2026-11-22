<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use App\Notifications\NewReviewReceived;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function create(Order $order): View
    {
        Gate::authorize('create-review', $order);

        $order->load(['laptop', 'seller']);

        return view('reviews.create', compact('order'));
    }

    public function store(StoreReviewRequest $request, Order $order): RedirectResponse
    {
        $review = DB::transaction(function () use ($request, $order) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            Gate::authorize('create-review', $lockedOrder);

            $review = new Review([
                'rating' => $request->validated('rating'),
                'comment' => $request->validated('comment'),
            ]);
            $review->order()->associate($lockedOrder);
            $review->reviewer()->associate($request->user());
            $review->seller()->associate($lockedOrder->seller);
            $review->save();

            return $review;
        });
        $order->seller->notify(new NewReviewReceived($review));

        return redirect()->route('orders.my')->with('success', 'Thank you. Your review has been submitted.');
    }

    public function seller()
    {
        $reviews = request()->user()->reviewsReceived()
            ->with(['reviewer', 'order.laptop'])
            ->latest()
            ->paginate(10);

        return view('reviews.seller', compact('reviews'));
    }
}
