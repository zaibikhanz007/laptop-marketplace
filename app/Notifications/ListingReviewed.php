<?php

namespace App\Notifications;

use App\Models\Laptop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ListingReviewed extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Laptop $laptop) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        $message = "Your listing {$this->laptop->brand} {$this->laptop->model} was {$this->laptop->status}.";
        if ($this->laptop->rejection_reason) {
            $message .= " Reason: {$this->laptop->rejection_reason}";
        }

        return ['message' => $message, 'url' => route('laptops.mine')];
    }
}
