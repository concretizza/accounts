<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stripe\Subscription as StripeSubscription;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'custom_status',
        'ends_at',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'ends_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeIsActive($query)
    {
        $query->whereIn('stripe_status', [
            StripeSubscription::STATUS_ACTIVE, StripeSubscription::STATUS_CANCELED,
        ])->where('ends_at', '>=', Carbon::now()->subDay());
    }
}
