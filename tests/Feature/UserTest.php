<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Hierarchy;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker, Hierarchy;

    public function test_user_register(): void
    {
        $user = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
        ];

        $response = $this->json('POST', '/api/register', $user);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('users', [
            'name' => $user['name'],
            'email' => $user['email'],
        ]);
    }

    public function test_user_me(): void
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/api/users/me');
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson(
            fn (AssertableJson $json) => $json->has('id')
                ->where('name', $user->name)
                ->where('email', $user->email)
                ->has('account_id')
                ->has('email_verified_at')
                ->has('created_at')
                ->has('updated_at')
        );
    }

    public function test_user_unverified_email(): void
    {
        $user = $this->createUser();
        $user->email_verified_at = null;
        $user->save();
        $response = $this->actingAs($user)->get('/api/users/me');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_user_create(): void
    {
        $user = $this->createUser();
        $userNew = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
        $this->createSubscription($user->account);

        $response = $this->actingAs($user)->json('POST', '/api/users', $userNew);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('users', [
            'account_id' => $user->account_id,
            'name' => $userNew['name'],
            'email' => $userNew['email'],
        ]);
    }

    public function test_user_update(): void
    {
        $user = $this->createUser();
        $this->createSubscription($user->account);
        $name = $this->faker->name;

        $response = $this->actingAs($user)->json('PUT', '/api/users/' . $user->id, ['name' => $name]);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('users', [
            'account_id' => $user->account_id,
            'name' => $name,
            'email' => $user->email,
        ]);
    }

    public function test_user_delete(): void
    {
        $user = $this->createUser();
        $userNew = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
        $this->createSubscription($user->account);

        $response = $this->actingAs($user)->json('POST', '/api/users', $userNew);
        $response->assertStatus(Response::HTTP_CREATED);

        $id = $response['user']['id'];

        $userCheck = [
            'id' => $id,
            'deleted_at' => null,
        ];

        $this->assertDatabaseHas('users', $userCheck);

        $response = $this->actingAs($user)->json('DELETE', '/api/users/' . $id);
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('users', $userCheck);
    }

    public function test_user_members(): void
    {
        $user = $this->createUser();
        $userNew = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
        $this->createSubscription($user->account);

        $response = $this->actingAs($user)->json('POST', '/api/users', $userNew);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('users', [
            'account_id' => $user->account_id,
            'name' => $userNew['name'],
            'email' => $userNew['email'],
        ]);

        $response = $this->actingAs($user)->json('GET', '/api/users');
        $response->assertStatus(Response::HTTP_OK);

        $response
            ->assertJson(
                fn (AssertableJson $json) => $json->has('users.data', 2)
            );
    }
}
