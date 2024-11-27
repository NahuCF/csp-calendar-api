<?php

namespace App\Providers;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client as TwilioClient;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register the Twilio channel for notifications
        Notification::extend('twilio', function ($app) {
            return new class
            {
                public function send($notifiable, $notification)
                {
                    $message = $notification->toTwilio($notifiable);

                    $twilio = new TwilioClient(
                        config('services.twilio.sid'),
                        config('services.twilio.token')
                    );

                    $twilio->messages->create(
                        $message['to'],
                        [
                            'from' => $message['from'],
                            'body' => $message['body'],
                        ]
                    );
                }
            };
        });
    }
}
