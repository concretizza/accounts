<?php

namespace App\Http\Controllers\Payments;

use App\Enums\SubscriptionStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;

class StripeSubscriptionCancelsController extends Controller
{
    public function destroy(Request $request, int $id)
    {
        $subscription = Subscription::findOrFail($id);

        if ($request->user()->cannot('change', $subscription)) {
            return response()->json([
                'message' => trans('auth.forbidden'),
            ], Response::HTTP_FORBIDDEN);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        $stripe->subscriptions->cancel($subscription->stripe_id, []);

        $subscription->fill([
            'stripe_status' => StripeSubscription::STATUS_CANCELED,
            'custom_status' => SubscriptionStatusEnum::CANCEL->value,
            'ends_at' => Carbon::now(),
        ])->save();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
