<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    |
    | Payment related response messages.
    |
    */

    'checkout_failed' => 'We could not process the checkout request, please try again',
    'checkout_customer_bad_request' => 'The customer account is incomplete',
    'checkout_price_bad_request' => 'The price is invalid',
    'subscription_invalid' => 'There is no active subscription',
    'subscription_cancelled' => 'The subscription was successfully cancelled',
    'subscription_refunded' => 'The subscription was successfully refunded',
    'subscription_not_cancelled_for_refund' => 'The subscription is not cancelled yet',
    'subscription_already_active' => 'There is an active subscription associated with user',

];
