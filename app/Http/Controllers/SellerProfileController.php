<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class SellerProfileController extends Controller
{
    public function show(User $user): View
    {
        $seller = User::query()
            ->withCount('reviewsReceived')
            ->withAvg('reviewsReceived', 'rating')
            ->findOrFail($user->id);

        $laptops = $seller->laptops()
            ->with(['images', 'seller' => fn ($query) => $query->withCount('reviewsReceived')->withAvg('reviewsReceived', 'rating')])
            ->where('status', 'available')
            ->where('approval_status', 'approved')
            ->latest()
            ->paginate(12, ['*'], 'laptops_page');

        $reviews = $seller->reviewsReceived()
            ->with('reviewer')
            ->latest()
            ->paginate(10, ['*'], 'reviews_page');

        $wishlistedIds = auth()->check() ? auth()->user()->wishlists()->pluck('laptop_id')->all() : [];
        $compareIds = session('compare_laptops', []);

        return view('sellers.show', compact('seller', 'laptops', 'reviews', 'wishlistedIds', 'compareIds'));
    }
}
