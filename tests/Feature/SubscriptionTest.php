<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\Hierarchy;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase, WithFaker, Hierarchy;

    public function test_with_plan(): void
    {
        $user = $this->createUser();
        $userNew = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
        $this->createSubscription($user->account);

        $plans = config('plans');
        $price_id = array_keys($plans)[1];
        $user->account->subscriptions()->first()->update([
            'stripe_price' => $price_id,
        ]);

        $response = $this->actingAs($user)->json('POST', '/api/users', $userNew);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('users', [
            'account_id' => $user->account_id,
            'name' => $userNew['name'],
            'email' => $userNew['email'],
        ]);

        $user->refresh();
        $this->assertTrue($user->account->hasActiveSubscription());
        $this->assertEquals(1, $user->account->countActiveSubscription());
        $this->assertEquals($plans['name'], $user->account->currentPlan()['name']);
    }

    public function test_no_plan(): void
    {
        $user = $this->createUser();
        $userNew = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
        $this->createSubscription($user->account);

        $response = $this->actingAs($user)->json('POST', '/api/users', $userNew);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('users', [
            'account_id' => $user->account_id,
            'name' => $userNew['name'],
            'email' => $userNew['email'],
        ]);

        $user->refresh();
        $this->assertTrue($user->account->hasActiveSubscription());
        $this->assertEquals(1, $user->account->countActiveSubscription());
        $this->assertEquals('No subscription', $user->account->currentPlan()['name']);
    }
}
