<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Payments\StripeCustomersController;
use App\Http\Controllers\Payments\StripeSubscriptionCancelsController;
use App\Http\Controllers\Payments\StripeSubscriptionCheckoutsController;
use App\Http\Controllers\Payments\StripeSubscriptionRefundsController;
use App\Http\Controllers\Payments\StripeWebHooksController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UsersMeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [UsersController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/recover', [AuthController::class, 'recover']);
Route::post('/reset', [AuthController::class, 'reset'])->name('password.reset');
Route::get('/verify/{token}', [UsersController::class, 'verify']);
Route::post('/payments/stripe/webhooks', [
    StripeWebHooksController::class, 'handle',
]);

Route::group(['middleware' => [
    'auth:sanctum', 'auth.token', 'auth.cookie', 'verified'],
], function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::delete('/logout', [AuthController::class, 'logout']);

    Route::post('/payments/stripe/checkouts/subscriptions', [
        StripeSubscriptionCheckoutsController::class, 'create',
    ]);
    Route::post('/payments/stripe/subscriptions/{id}/refunds', [
        StripeSubscriptionRefundsController::class, 'create',
    ]);

    Route::get('/users/me', [UsersMeController::class, 'show']);
    Route::resource('/users', UsersController::class)->except(['create']);

    Route::group(['middleware' => ['subscribed']], function () {
        Route::post('/users', [UsersController::class, 'create']);

        Route::post('/payments/stripe/customers/dashboard', [
            StripeCustomersController::class, 'dashboard',
        ]);
        Route::delete('/payments/stripe/subscriptions/{id}', [
            StripeSubscriptionCancelsController::class, 'destroy',
        ]);
    });
});
