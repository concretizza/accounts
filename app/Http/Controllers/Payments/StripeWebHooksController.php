<?php

namespace App\Http\Controllers\Payments;

use App\Enums\AccountSettingsEnum;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Setting;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Invoice;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;
use Stripe\Webhook;

class StripeWebHooksController extends Controller
{
    public function handle(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        [$event, $errCode] = $this->getEvent($request);

        if ($errCode > 0) {
            return response()->json([], $errCode);
        }

        $this->handleEvent($event);

        return response()->json([]);
    }

    private function getEvent(Request $request): array
    {
        $webhookSecret = config('services.stripe.webhook');
        $signature = $request->header('stripe-signature');
        $payload = $request->getContent();

        $res = [null, 0];

        try {
            $res[0] = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            $res[1] = 400;
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            $res[1] = 400;
        }

        return $res;
    }

    private function handleEvent(Event $event): Response
    {
        $method = 'handle'.Str::studly(str_replace('.', '_', $event->type));

        if (method_exists($this, $method)) {
            return $this->{$method}($event->data->object);
        }

        return new Response('webhook event unhandled', Response::HTTP_OK);
    }

    private function handleCustomerSubscriptionCreated(StripeSubscription $payload): Response
    {
        $setting = Setting::byKeyValue(AccountSettingsEnum::STRIPE_CUSTOMER_ID->value, $payload->customer)->first();
        $account = Account::findOrFail($setting->settingable_id);
        $item = $payload->items->data[0];
        Subscription::create([
            'account_id' => $account->id,
            'stripe_id' => $payload->id,
            'stripe_status' => $payload->status,
            'stripe_price' => $item->price->id,
        ]);

        return $this->successResponse();
    }

    private function handleCustomerSubscriptionUpdated(StripeSubscription $payload): Response
    {
        $setting = Setting::byKeyValue(AccountSettingsEnum::STRIPE_CUSTOMER_ID->value, $payload->customer)->first();
        $account = Account::findOrFail($setting->settingable_id);
        $subscription = $account->subscriptions()->firstOrNew(['stripe_id' => $payload->id]);
        $item = $payload->items->data[0];

        $subscription->stripe_status = $payload->status ?? StripeSubscription::STATUS_INCOMPLETE;
        $subscription->stripe_price = $item->price->id;
        $subscription->ends_at = Carbon::createFromTimestamp($payload->current_period_end);

        $subscription->save();

        return $this->successResponse();
    }

    private function handleCustomerSubscriptionDeleted(StripeSubscription $payload): Response
    {
        $setting = Setting::byKeyValue(AccountSettingsEnum::STRIPE_CUSTOMER_ID->value, $payload->customer)->first();
        $account = Account::findOrFail($setting->settingable_id);
        $account->subscriptions()->each(function (Subscription $subscription) {
            $subscription->fill([
                'stripe_status' => StripeSubscription::STATUS_CANCELED,
                'ends_at' => Carbon::now(),
            ])->save();
        });

        return $this->successResponse();
    }

    private function handleInvoicePaymentSucceeded(Invoice $payload): Response
    {
        $setting = Setting::byKeyValue(AccountSettingsEnum::STRIPE_CUSTOMER_ID->value, $payload->customer)->first();
        $account = Account::findOrFail($setting->settingable_id);
        $subscription = $account->subscriptions()->firstOrNew(['stripe_id' => $payload->subscription]);

        $stripe = new StripeClient(config('services.stripe.secret'));
        $stripeSubscription = $stripe->subscriptions->retrieve($payload->subscription);

        $status = $payload->status ?? StripeSubscription::STATUS_INCOMPLETE;
        if ($status == 'paid') {
            $status = StripeSubscription::STATUS_ACTIVE;
        }

        $subscription->stripe_status = $status;
        $subscription->ends_at = Carbon::createFromTimestamp($stripeSubscription->current_period_end);

        $subscription->save();

        return $this->successResponse();
    }

    private function successResponse(): Response
    {
        return new Response('webhook event successfully handled', Response::HTTP_OK);
    }
}
