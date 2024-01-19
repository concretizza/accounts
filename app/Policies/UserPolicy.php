<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $member): bool
    {
        return $user->account_id === $member->account_id;
    }

    public function change(User $user, User $member): bool
    {
        if (! $user->account->users()->count()) {
            return false;
        }

        return $this->view($user, $member);
    }
}
