<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ResetPasswordSMS extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = [10, 60];

    public function __construct(
        public string $token,
        public string $phone
    ) {}

    public function via($notifiable): array
    {
        return ['twilio'];
    }

    public function toTwilio($notifiable): array
    {
        return [
            'from' => config('services.twilio.phone_number'),
            'to' => $this->phone,
            'body' => "Your password reset code is: {$this->token}\n\nThis code will expire in 15 minutes.",
        ];
    }
}
