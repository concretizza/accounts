<?php

namespace Tests;

use App\Models\Account;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

trait Hierarchy
{
    use WithFaker;

    public function createAccount(): Account
    {
        return Account::factory()->create();
    }

    public function createUser(): User
    {
        return User::factory()->for($this->createAccount())->create();
    }

    public function createSubscription(Account $account): Subscription
    {
        return Subscription::factory()->for($account)->create();
    }
}
