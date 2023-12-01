<?php

namespace App\Services;

use App\Models\User;

class UserEmailVerificationService
{
    public static function encodeEmailVerification(User $user): string
    {
        return base64_encode(strval($user->id).'.'.base64_encode($user->created_at));
    }

    public static function decodeEmailVerification(string $token): array
    {
        $plainText = base64_decode($token);
        $plainText = explode('.', $plainText);
        $issued = base64_decode($plainText[1]);

        return [$plainText[0], $issued];
    }
}
