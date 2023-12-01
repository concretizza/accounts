<?php

namespace App\Http\Controllers;

use App\Enums\AuthEnum;
use App\Http\Requests\AuthenticationLoginRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function login(AuthenticationLoginRequest $request)
    {
        $req = $request->only('email', 'password');
        $user = User::where('email', $req['email'])->first();

        if (! $user || ! Hash::check($req['password'], $user->password)) {
            return response()->json([
                trans('auth.failed'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->email_verified_at) {
            return response()->json([
                trans('auth.verified'),
            ], Response::HTTP_FORBIDDEN);
        }

        $user['access_token'] = $user->createToken($request->header('User-Agent') ?? 'unknown')->plainTextToken;

        $cookie = cookie(AuthEnum::COOKIE->value, $user['access_token']);

        return response()->json($user)->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        if ($request->user()->tokens()->delete()) {
            return response()->json([
                'message' => trans('auth.logout'),
            ]);
        }

        return response()->json([
            'message' => trans('common.failed'),
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function recover(Request $request)
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => trans('passwords.sent'),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => trans('common.failed'),
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
            'id' => 'required',
        ]);

        $user = User::where('id', Crypt::decryptString($request->input('id')))->first();
        $request->merge(['email' => $user->email]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if (! $user->email_verified_at) {
                $user->update(['email_verified_at' => Carbon::now()]);
            }

            return response()->json([
                'message' => trans('passwords.reset'),
            ], Response::HTTP_OK);
        }

        if ($status == Password::INVALID_TOKEN) {
            return response()->json([
                'message' => trans('passwords.token'),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => trans('common.failed'),
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
