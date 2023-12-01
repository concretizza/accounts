<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function change(User $user, User $member): bool
    {
        if (!$user->account->users()->count()) {
            return false;
        }

        return $user->account_id === $member->account_id;
    }
}
