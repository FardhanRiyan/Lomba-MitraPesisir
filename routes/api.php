<?php

use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\CommunityApiController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrf;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->withoutMiddleware([ValidateCsrf::class])
    ->prefix('v1')
    ->group(function () {
        Route::middleware('guest')->group(function () {
            Route::post('/login', [AuthApiController::class, 'login']);
            Route::post('/register', [AuthApiController::class, 'register']);
        });

        Route::get('/dashboard', [DashboardApiController::class, 'index']);
        Route::get('/predictions', [DashboardApiController::class, 'prediction']);
        Route::get('/community/posts', [CommunityApiController::class, 'index']);

        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::get('/me', [AuthApiController::class, 'me']);
        Route::patch('/profile', [AuthApiController::class, 'profile']);
        Route::patch('/password', [AuthApiController::class, 'password']);
        Route::patch('/preferences', [AuthApiController::class, 'preferences']);

        Route::post('/community/posts', [CommunityApiController::class, 'store']);
        Route::post('/community/posts/{post}/replies', [CommunityApiController::class, 'reply']);
        Route::delete('/community/posts/{post}', [CommunityApiController::class, 'destroy']);
    });
