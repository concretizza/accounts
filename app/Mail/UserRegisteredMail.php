<?php

namespace App\Mail;

use App\Models\User;
use App\Services\UserEmailVerificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserRegisteredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $verification;

    public function __construct(public User $user)
    {
        $this->verification = $this->generateVerificationToken();
    }

    private function generateVerificationToken(): string
    {
        return UserEmailVerificationService::encodeEmailVerification($this->user);
    }

    public function envelope(): Envelope
    {
        $name = explode(' ', $this->user->name)[0];

        return new Envelope(
            subject: trans('mail.greeting').' '.$name.'!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.users.confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
