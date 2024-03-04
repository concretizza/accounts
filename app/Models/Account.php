<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            $account->uuid = Str::uuid();
        });
    }

    protected $fillable = [
        'uuid',
        'title',
        'icon',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function settings()
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

    public function hasActiveSubscription(): bool
    {
        return (bool) $this->countActiveSubscription();
    }

    public function countActiveSubscription(): int
    {
        $subscription = $this->subscriptions();
        if (! $subscription) {
            return 0;
        }

        return $subscription->isActive()->count();
    }

    public function currentPlan(): array
    {
        $subscription = $this->subscriptions()->isActive()->latest()->first();

        $price = '';
        if ($subscription) {
            $price = $subscription->stripe_price;
        }

        $plan = config('plans.'.$price) ?? config('plans.price_none');
        if ($subscription) {
            $plan['ends_at'] = $subscription->ends_at->format('d/m/Y');
        }

        return $plan;
    }
}
