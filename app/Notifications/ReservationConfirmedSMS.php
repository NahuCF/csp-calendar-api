<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReservationConfirmedSMS extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $number,
        public string $resource,
        public string $facility,
        public string $start_date,
        public string $end_date,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['twilio'];
    }

    public function toTwilio(object $notifiable): array
    {
        return [
            'from' => config('services.twilio.phone_number'),
            'to' => $this->number,
            'body' => "Your reservation has been confirmed.\n\n".
                "Location: {$this->facility} - {$this->resource}\n".
                "From: {$this->start_date}\n".
                "To: {$this->end_date}\n",
        ];
    }
}
