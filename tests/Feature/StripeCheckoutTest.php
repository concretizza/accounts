<?php

namespace Tests\Feature;

use App\Mail\UserRegisteredMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Environment;
use Tests\Hierarchy;
use Tests\StripeSetup;
use Tests\TestCase;

class StripeCheckoutTest extends TestCase
{
    use RefreshDatabase, WithFaker, Hierarchy, StripeSetup, Environment;

    public function test_create_checkout(): void
    {
        $this->runOnlyIntegrations();

        Mail::fake();

        $user = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
        ];

        $response = $this->json('POST', '/api/register', $user);

        $response->assertStatus(Response::HTTP_CREATED);

        Mail::assertQueued(UserRegisteredMail::class, function (UserRegisteredMail $mail) use ($response) {
            $response = $this->json('GET', '/api/verify/'.$mail->verification);
            $response->assertJson(fn (AssertableJson $json) => $json->where('message', trans('user.verify_success')));

            return $mail->assertSeeInHtml($mail->verification);
        });

        $userCreated = User::where('email', $user['email'])->first();

        $response = $this->actingAs($userCreated)->json(
            'POST',
            '/api/payments/stripe/checkouts',
            ['price_id' => self::PRICE_ID],
        );

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson(fn (AssertableJson $json) => $json->has('url'));
    }

    public function test_create_checkout_without_customer(): void
    {
        $this->runOnlyIntegrations();

        $user = $this->createUser();
        $response = $this->actingAs($user)->json(
            'POST',
            '/api/payments/stripe/checkouts',
            []
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        $message = trans('payment.checkout_customer_bad_request');
        $response->assertJson(fn (AssertableJson $json) => $json->where('message', $message));
    }
}
