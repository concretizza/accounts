<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeService
{
    public static function createCustomer(string $name, string $email): string
    {
        $stripe = new StripeClient(config('services.stripe.secret'));

        $info = [
            'name' => $name,
            'email' => $email,
        ];

        if (config('services.stripe.test_clock_id') && config('app.env') != 'production') {
            $info['test_clock'] = config('services.stripe.test_clock_id');
        }

        $customer = $stripe->customers->create($info);

        return $customer->id;
    }
}
