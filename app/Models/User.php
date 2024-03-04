<?php

namespace App\Models;

use App\Notifications\PasswordResetNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->uuid = Str::uuid();
        });
    }

    protected $fillable = [
        'account_id',
        'uuid',
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'settings' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token, $this));
    }
}
