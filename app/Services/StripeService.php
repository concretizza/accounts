<?php

namespace App\Services;

use App\Enums\CheckoutSessionModeEnum;
use Stripe\Checkout\Session;
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

        if (
            config('services.stripe.test_clock_id') &&
            config('app.env') != 'production'
        ) {
            $info['test_clock'] = config('services.stripe.test_clock_id');
        }

        $customer = $stripe->customers->create($info);

        return $customer->id;
    }

    public static function createSessionUrl(
        CheckoutSessionModeEnum $mode,
        string $customerId,
        string $priceId,
        int $quantity,
    ): string {
        $checkoutSession = Session::create([
            'mode' => $mode->value,
            'customer' => $customerId,
            'currency' => config('services.stripe.currency'),
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => $quantity,
                ],
            ],
            'success_url' => config('app.client_url').'/subscriptions/success',
            'cancel_url' => config('app.client_url').'/subscriptions/cancel',
        ]);

        return $checkoutSession->url;
    }
}
