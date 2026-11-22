<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NewReviewReceived extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Review $review) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "You received a {$this->review->rating}-star review.",
            'url' => route('reviews.seller'),
        ];
    }
}
