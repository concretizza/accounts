<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AccessTokenService
{
    const V1 = 1;

    public static function encode(User $user, string $tok): string
    {
        $now = time();
        $accessToken = [
            'ver' => self::V1,
            'iss' => config('app.url'),
            'aud' => config('app.url'),
            'iat' => $now,
            'exp' => $now + (int) config('app.security.exp'),
            'sub' => $user->id,
            'acc' => $user->account->id,
            'tok' => $tok,
        ];

        $privateKey = file_get_contents(config('app.security.pri'));
        return JWT::encode($accessToken, $privateKey, config('app.security.alg'));
    }

    public static function decode(string $tok)
    {
        $publicKey = file_get_contents(config('app.security.pub'));
        return JWT::decode($tok, new Key($publicKey, config('app.security.alg')));
    }
}
