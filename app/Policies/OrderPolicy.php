<?php

namespace App\Policies;

use App\Models\Laptop;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function create(User $user, Laptop $laptop): bool
    {
        if ($user->status !== 'active') {
            return false;
        }

        if ($laptop->status !== 'available' || $laptop->approval_status !== 'approved') {
            return false;
        }

        if ($laptop->user_id === $user->id) {
            return false;
        }

        return ! Order::where('laptop_id', $laptop->id)
            ->where('buyer_id', $user->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();
    }

    public function accept(User $user, Order $order): bool
    {
        return ($order->seller_id === $user->id || $user->isAdmin())
            && $order->status === 'pending';
    }

    public function reject(User $user, Order $order): bool
    {
        return ($order->seller_id === $user->id || $user->isAdmin())
            && $order->status === 'pending';
    }

    public function complete(User $user, Order $order): bool
    {
        return ($order->seller_id === $user->id || $user->isAdmin())
            && $order->status === 'accepted';
    }

    public function cancel(User $user, Order $order): bool
    {
        return ($order->buyer_id === $user->id || $user->isAdmin())
            && in_array($order->status, ['pending', 'accepted'], true);
    }

    public function viewBuyer(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id;
    }

    public function viewSeller(User $user, Order $order): bool
    {
        return $order->seller_id === $user->id;
    }

    public function message(User $user, Order $order): bool
    {
        if (! in_array($order->status, ['pending', 'accepted'], true)) {
            return false;
        }

        return $user->isAdmin()
            || $order->buyer_id === $user->id
            || $order->seller_id === $user->id;
    }
}
