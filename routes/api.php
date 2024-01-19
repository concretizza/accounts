<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Payments\StripeCustomersController;
use App\Http\Controllers\Payments\StripeSubscriptionCheckoutsController;
use App\Http\Controllers\Payments\StripeSubscriptionsController;
use App\Http\Controllers\Payments\StripeWebHooksController;
use App\Http\Controllers\UsersController;
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
Route::post('/payments/stripe/webhooks', [StripeWebHooksController::class, 'handle']);

Route::group(['middleware' => ['auth:sanctum', 'verified']], function () {
    Route::get('/users/me', [UsersController::class, 'show']);
    Route::delete('/logout', [AuthController::class, 'logout']);
    Route::post('/payments/stripe/checkouts/subscriptions', [
        StripeSubscriptionCheckoutsController::class, 'create',
    ]);
    Route::post('/payments/stripe/subscriptions/{id}/refunds', [StripeSubscriptionsController::class, 'refund']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/users', [UsersController::class, 'index']);
    Route::put('/users/{id}', [UsersController::class, 'update']);

    Route::group(['middleware' => ['subscribed']], function () {
        Route::post('/users', [UsersController::class, 'create']);
        Route::post('/payments/stripe/customers/dashboard', [StripeCustomersController::class, 'dashboard']);
        Route::delete('/payments/stripe/subscriptions/{id}', [StripeSubscriptionsController::class, 'cancel']);
        Route::delete('/users/{id}', [UsersController::class, 'destroy']);
    });
});
