<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stripe_id' => 'sub_'.Str::uuid(),
            'stripe_price' => 'price_'.Str::uuid(),
            'stripe_status' => 'active',
            'ends_at' => now()->addMonth(),
        ];
    }
}
