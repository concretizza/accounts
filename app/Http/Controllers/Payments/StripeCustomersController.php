<?php

namespace App\Http\Controllers\Payments;

use App\Enums\AccountSettingsEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\StripeClient;

class StripeCustomersController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        if ($user->owner) {
            return response()->json([
                'message' => trans('user.forbidden_subscription_dashboard'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $customer = $user->account->settings()
            ->byKey(AccountSettingsEnum::STRIPE_CUSTOMER_ID->value)
            ->first();
        $customerId = $customer->value ?? null;
        
        $stripe = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => config('app.client_url'),
        ]);

        return response()->json(['url' => $session->url], Response::HTTP_CREATED);
    }
}
