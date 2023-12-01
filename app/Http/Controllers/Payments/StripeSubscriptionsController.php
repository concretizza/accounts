<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;

class StripeSubscriptionsController extends Controller
{
    public function cancel(Request $request, int $id)
    {
        $subscription = Subscription::findOrFail($id);

        if ($request->user()->cannot('change', $subscription)) {
            return response()->json(['message' => trans('auth.forbidden')], Response::HTTP_FORBIDDEN);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        $stripe->subscriptions->cancel($subscription->stripe_id, []);

        $subscription->fill([
            'stripe_status' => StripeSubscription::STATUS_CANCELED,
            'ends_at' => Carbon::now(),
        ])->save();

        return response()->json(['message' => trans('payment.subscription_cancelled')]);
    }

    public function refund(Request $request, int $id)
    {
        $subscription = Subscription::findOrFail($id);

        if ($subscription->stripe_status != StripeSubscription::STATUS_CANCELED) {
            return response()->json(['message' => trans('payment.subscription_not_cancelled_for_refund')], Response::HTTP_BAD_REQUEST);
        }

        if ($request->user()->cannot('change', $subscription)) {
            return response()->json(['message' => trans('auth.forbidden')], Response::HTTP_FORBIDDEN);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        $stripeSubscription = $stripe->subscriptions->retrieve($subscription->stripe_id, []);
        $latestInvoiceId = $stripeSubscription->latest_invoice;
        $invoice = $stripe->invoices->retrieve($latestInvoiceId, ['expand' => ['charge']]);

        $stripe->refunds->create([
            'charge' => $invoice->charge->id,
        ]);

        $subscription->fill([
            'stripe_status' => StripeSubscription::STATUS_CANCELED,
            'ends_at' => Carbon::now(),
        ])->save();

        return response()->json(['message' => trans('payment.subscription_refunded')]);
    }
}
