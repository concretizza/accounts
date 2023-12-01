<?php

namespace App\Notifications;

use App\Mail\UserPasswordResetMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token, public User $user)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): UserPasswordResetMail
    {
        return new UserPasswordResetMail($this->token, $this->user);
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
