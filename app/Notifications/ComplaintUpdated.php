<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ComplaintUpdated extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Complaint $complaint) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your complaint has been updated to {$this->complaint->status}.",
            'url' => route('complaints.index'),
        ];
    }
}
