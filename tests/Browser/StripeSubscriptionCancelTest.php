<?php

namespace Tests\Browser;

use App\Enums\AccountSettingsEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Models\User;
use App\Services\StripeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Dusk\Browser;
use Stripe\Subscription as StripeSubscription;
use Tests\DuskTestCase;
use Tests\Environment;
use Tests\Hierarchy;
use Tests\StripeSetup;

class StripeSubscriptionCancelTest extends DuskTestCase
{
    use Hierarchy, DatabaseMigrations, StripeSetup, Environment;

    public function test_subscribe_cancel(): void
    {
        $this->runOnlyIntegrations();

        $user = $this->createUser();

        $account = $user->account;
        $account->settings()->create([
            'key' => AccountSettingsEnum::STRIPE_CUSTOMER_ID->value,
            'value' => StripeService::createCustomer($user->name, $user->email),
        ]);

        $user->update([
            'email_verified_at' => Carbon::now(),
        ]);

        $userCreated = User::where('email', $user['email'])->first();

        $response = $this->actingAs($userCreated)->json(
            'POST',
            '/api/payments/stripe/checkouts/subscriptions',
            ['price_id' => self::PRICE_ID],
        );

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson(fn (AssertableJson $json) => $json->has('url'));

        $checkoutUrl = $response['url'];

        $this->browse(function (Browser $browser) use ($checkoutUrl, $userCreated) {
            $browser->visit($checkoutUrl)
                ->waitFor('.App-Payment')
                ->assertSee(self::PRICE_DESCRIPTION)
                ->type('#billingName', $userCreated->name)
                ->type('#cardNumber', self::CARD_VALID_NUMBER)
                ->type('#cardExpiry', '0140')
                ->type('#cardCvc', '123')
                ->press('button.SubmitButton')
                ->pause(10000)
                ->assertPathIs('/subscriptions/success');
        });

        $active = false;
        for ($i = 0; $i < 5; $i++) {
            $account->refresh();

            if ($account->subscriptions()->isActive()->count() > 0) {
                $active = true;
                break;
            }

            sleep(1);
        }

        $this->assertTrue($active);

        $subscription = $account->subscriptions()->latest()->first();

        $response = $this->actingAs($userCreated)->json(
            'DELETE',
            '/api/payments/stripe/subscriptions/'.$subscription->id,
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $subscription->refresh();

        $this->assertEquals(StripeSubscription::STATUS_CANCELED, $subscription->stripe_status);
        $this->assertEquals(SubscriptionStatusEnum::CANCEL->value, $subscription->custom_status);
    }
}
