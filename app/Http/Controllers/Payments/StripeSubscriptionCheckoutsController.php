<?php

namespace App\Http\Controllers\Payments;

use App\Enums\AccountSettingsEnum;
use App\Enums\CheckoutSessionModeEnum;
use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;

class StripeSubscriptionCheckoutsController extends Controller
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

            if (! $customerId) {
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
            $checkoutUrl = StripeService::createSessionUrl(
                CheckoutSessionModeEnum::SUB,
                $customerId,
                $priceId,
                1,
            );

            return response()->json([
                'url' => $checkoutUrl,
            ], Response::HTTP_CREATED);
        } catch (InvalidRequestException $e) {
            Log::warning('stripe checkout invalid request: {code}: {message}', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => trans('payment.checkout_price_bad_request'),
            ], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            Log::error('stripe checkout: {code}: {message}', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => trans('payment.checkout_failed'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
