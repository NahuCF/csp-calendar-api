<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $token,
        public string $email
    ) {}

    public function build()
    {
        return $this->subject('Reset Password Request')
            ->view('emails.reset-password')
            ->with([
                'resetUrl' => config('app.frontend_url').'/reset-password?token='.$this->token.'&email='.$this->email,
            ]);
    }
}
