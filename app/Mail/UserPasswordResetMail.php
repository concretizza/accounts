<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;

class UserPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $link;

    public function __construct(public string $token, public User $user)
    {
        $id = Crypt::encryptString($user->id);
        $this->link = config('app.client_url').'/reset?token='.$this->token.'&id='.$id;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('mail.reset'),
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            to: $this->user->email,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.users.reset',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
