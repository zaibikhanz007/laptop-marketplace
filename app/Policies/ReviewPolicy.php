<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id
            && $order->status === 'completed'
            && ! Review::where('order_id', $order->id)->exists();
    }

    public function store(User $user, Order $order): bool
    {
        return $this->create($user, $order);
    }
}
