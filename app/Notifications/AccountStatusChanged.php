<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AccountStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $status) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->status === 'active' ? 'Your account has been approved.' : 'Your account has been blocked.',
            'url' => route('dashboard'),
        ];
    }
}
