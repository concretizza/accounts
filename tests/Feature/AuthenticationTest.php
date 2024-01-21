<?php

namespace Tests\Feature;

use App\Mail\UserRegisteredMail;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Hierarchy;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker, Hierarchy;

    public function test_auth_access_verified_account(): void
    {
        Mail::fake();

        $user = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
        ];

        $response = $this->json('POST', '/api/register', $user);

        $response->assertStatus(Response::HTTP_CREATED);

        Mail::assertQueued(UserRegisteredMail::class, function (
            UserRegisteredMail $mail,
        ) use ($user, $response) {
            $id = $response['user']['id'];
            $response = $this->json('GET', '/api/verify/'.$mail->verification);

            $response->assertJson(
                fn (AssertableJson $json) => $json->where('message', trans('user.verify_success'),
                ),
            );

            $userCreated = User::findOrFail($id);
            $this->assertNotNull($userCreated->email_verified_at);

            $name = explode(' ', $user['name'])[0];
            $subject = trans('mail.greeting').' '.$name.'!';

            return $mail->hasTo($user['email']) &&
                $mail->hasSubject($subject) &&
                $mail->assertSeeInHtml($mail->verification);
        });

        $response = $this->json('POST', '/api/login', $user);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson(
            fn (AssertableJson $json) => $json->has('id')
                ->where('name', $user['name'])
                ->where('email', $user['email'])
                ->has('email_verified_at')
                ->has('access_token')
                ->etc()
        );
    }

    public function test_auth_recover(): void
    {
        Notification::fake();

        $user = $this->createUser();

        $response = $this->json('POST', '/api/recover', [
            'email' => $user->email,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson(
            fn (AssertableJson $json) => $json->where('message', trans('passwords.sent')),
        );

        Notification::assertSentTo($user, PasswordResetNotification::class, function (
            PasswordResetNotification $notification,
        ) use ($user) {
            $response = $this->json('POST', '/api/reset', [
                'id' => Crypt::encryptString($user->id),
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'token' => $notification->token,
            ]);

            $response->assertStatus(Response::HTTP_OK);
            $response->assertJson(
                fn (AssertableJson $json) => $json->where('message', trans('passwords.reset')),
            );

            return true;
        });
    }

    public function test_auth_logout(): void
    {
        $password = 'password';
        $user = $this->createUser();
        $user->password = Hash::make($password);
        $user->save();

        $response = $this->json('POST', '/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);
        $response->assertStatus(Response::HTTP_OK);

        $accessToken = $response['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$accessToken,
        ])->get('/api/users/me');
        $response->assertStatus(Response::HTTP_OK);

        $user = $user->refresh();
        $this->assertEquals(1, $user->tokens()->count());

        $response = $this->delete('/api/logout');
        $response->assertStatus(Response::HTTP_OK);

        $user = $user->refresh();
        $this->assertEquals(0, $user->tokens()->count());
    }
}
