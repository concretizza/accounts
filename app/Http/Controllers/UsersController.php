<?php

namespace App\Http\Controllers;

use App\Enums\AccountSettingsEnum;
use App\Http\Requests\UserRegisterRequest;
use App\Mail\UserRegisteredMail;
use App\Models\Account;
use App\Models\User;
use App\Services\StripeService;
use App\Services\UserEmailVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $users = User::where('account_id', $user->account_id)->orderBy('name')->paginate();

        return response()->json([
            'users' => $users,
        ]);
    }

    public function show()
    {
        return User::findOrFail(Auth::user()->id);
    }

    public function register(UserRegisterRequest $request)
    {
        $req = $request->only('name', 'email', 'password');
        $req['password'] = Hash::make($req['password']);

        $userDB = User::where('email', $req['email'])->first();
        if ($userDB) {
            return response()->json([
                'message' => trans('user.registered'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $account = Account::create(['title' => $req['email']]);
        $user = $account->users()->create($req);
        if ($user) {
            Mail::to($user->email)->queue(new UserRegisteredMail($user));

            return response()->json([
                'user' => $user,
                'message' => trans('user.created'),
            ], Response::HTTP_CREATED);
        }

        return response()->json([
            'message' => trans('common.failed'),
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function verify(string $token)
    {
        return DB::transaction(function () use ($token) {
            $plainText = UserEmailVerificationService::decodeEmailVerification($token);
            if (count($plainText) != 2) {
                return response()->json([
                    'message' => trans('user.verify_invalid'),
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = User::where('id', $plainText[0])->where('created_at', $plainText[1])->first();

            if ($user) {
                if ($user->email_verified_at) {
                    return response()->json([
                        'message' => trans('user.verify_confirmed'),
                    ], Response::HTTP_BAD_REQUEST);
                }

                $user->account->settings()->create([
                    'key' => AccountSettingsEnum::STRIPE_CUSTOMER_ID->value,
                    'value' => StripeService::createCustomer($user->name, $user->email),
                ]);

                $user->update([
                    'email_verified_at' => Carbon::now(),
                ]);

                return response()->json([
                    'message' => trans('user.verify_success'),
                ], Response::HTTP_OK);
            }

            return response()->json([
                'message' => trans('common.failed'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'email',
        ]);

        $user = $request->user();
        $req = $request->only('name', 'email');
        $req['account_id'] = $user->account_id;
        $req['password'] = Hash::make(Str::random(8));

        $userDB = User::where('email', $req['email'])->first();
        if ($userDB) {
            return response()->json([
                'message' => trans('user.registered'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = User::create($req);
        if ($user) {
            $status = Password::sendResetLink($request->only('email'));

            if ($status !== Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => trans('common.failed'),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'user' => $user,
                'message' => trans('user.new'),
            ], Response::HTTP_CREATED);
        }

        return response()->json([
            'message' => trans('common.failed'),
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function update(Request $request, int $id)
    {
        $validate = [
            'name' => 'required',
            'email' => 'email',
        ];

        $password = '';
        if ($request->input('password')) {
            $validate['password'] = ['required', 'confirmed', 'min:8'];
            $password = Hash::make($request->input('password'));
        }

        $req = $request->validate($validate);

        if ($password != '') {
            $req['password'] = $password;
        }

        $userDB = User::findOrFail($id);
        if ($request->user()->cannot('change', $userDB)) {
            return response()->json(['message' => trans('auth.forbidden')], Response::HTTP_FORBIDDEN);
        }

        if ($userDB->update($req)) {
            return response()->json([
                'message' => trans('common.success'),
            ]);
        }

        return response()->json([
            'message' => trans('common.failed'),
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function destroy(Request $request, int $id)
    {
        $userDB = User::findOrFail($id);
        $user = $request->user();
        if ($request->user()->cannot('change', $userDB)) {
            return response()->json(['message' => trans('auth.forbidden')], Response::HTTP_FORBIDDEN);
        }

        $action = $userDB->owner ? $userDB->forceDelete() : $userDB->delete();

        if ($action) {
            return response(null, Response::HTTP_NO_CONTENT);
        }

        return response()->json([
            'message' => trans('common.failed'),
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
