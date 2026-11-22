<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NewPurchaseRequest extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "New purchase request for {$this->order->laptop->brand} {$this->order->laptop->model}.",
            'url' => route('orders.incoming'),
        ];
    }
}
