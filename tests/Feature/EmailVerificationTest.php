<?php

namespace Tests\Feature;

use App\Services\UserEmailVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Hierarchy;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase, Hierarchy;

    public function test_encode_and_decode_email(): void
    {
        $user = $this->createUser();
        $token = UserEmailVerificationService::encodeEmailVerification($user);
        $plainText = UserEmailVerificationService::decodeEmailVerification($token);
        $this->assertEquals($user->id, $plainText[0]);
        $this->assertEquals($user->created_at, $plainText[1]);
    }
}
