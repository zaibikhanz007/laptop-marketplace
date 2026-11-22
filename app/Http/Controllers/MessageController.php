<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Models\Order;
use App\Notifications\NewMessageReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request, Order $order): View
    {
        Gate::authorize('message', $order);

        if (! $request->user()->isAdmin()) {
            $order->messages()
                ->where('sender_id', '!=', $request->user()->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $order->load(['laptop', 'buyer', 'seller', 'messages.sender']);

        return view('messages.index', compact('order'));
    }

    public function store(StoreMessageRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('message', $order);
        abort_if($request->user()->isAdmin(), 403);

        $message = new Message($request->validated());
        $message->sender()->associate($request->user());
        $order->messages()->save($message);

        $order->loadMissing(['laptop', 'buyer', 'seller']);
        $receiver = $order->buyer_id === $request->user()->id
            ? $order->seller
            : $order->buyer;
        $receiver->notify(new NewMessageReceived($order));

        return redirect()->route('messages.index', $order);
    }
}
