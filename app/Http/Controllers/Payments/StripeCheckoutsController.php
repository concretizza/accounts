<?php

namespace App\Http\Controllers\Payments;

use App\Enums\AccountSettingsEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeCheckoutsController extends Controller
{
    public function create(Request $request)
    {
        try {
            $user = $request->user();
            Stripe::setApiKey(config('services.stripe.secret'));

            $setting = $user->account->settings()
                ->byKey(AccountSettingsEnum::STRIPE_CUSTOMER_ID->value)
                ->first();
            $customerId = $setting->value ?? null;

            if (!$customerId) {
                return response()->json([
                    'message' => trans('payment.checkout_customer_bad_request'),
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($user->account->subscriptions()->isActive()->count() > 0) {
                return response()->json([
                    'message' => trans('payment.subscription_already_active'),
                ], Response::HTTP_BAD_REQUEST);
            }

            $priceId = $request->input('price_id');
            $checkoutUrl = $this->createSessionUrl($customerId, $priceId);

            return response()->json(['url' => $checkoutUrl], Response::HTTP_CREATED);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            Log::warning('stripe checkout invalid request: {code}: {message}', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => trans('payment.checkout_price_bad_request'),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            Log::error('stripe checkout: {code}: {message}', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => trans('payment.checkout_failed'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createSessionUrl(string $customerId, string $priceId): string
    {
        $checkoutSession = Session::create([
            'mode' => 'subscription',
            'customer' => $customerId,
            'currency' => 'USD',
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => 1,
                ],
            ],
            'success_url' => config('app.client_url') . '/subscriptions/success',
            'cancel_url' => config('app.client_url') . '/subscriptions/cancel',
        ]);

        return $checkoutSession->url;
    }
}
