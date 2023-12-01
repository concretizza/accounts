<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function change(User $user, Subscription $subscription): bool
    {
        return $user->account_id === $subscription->account_id;
    }
}
