<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your order #{$this->order->id} is now {$this->order->status}.",
            'url' => $notifiable->getKey() === $this->order->seller_id
                ? route('orders.incoming')
                : route('orders.my'),
        ];
    }
}
