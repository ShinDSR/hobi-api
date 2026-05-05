<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\HobbyController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::controller(UserController::class)->prefix('users')->group(function () {
        Route::get('/', 'list');
        Route::get('/{user}', 'getByID');

        Route::middleware('admin')->group(function () {
            Route::post('/', 'create');
            Route::put('/{user}', 'update');
            Route::delete('/{user}', 'delete');

            Route::controller(HobbyController::class)->prefix('{user}/hobbies')->group(function () {
                Route::get('/', 'list');
                Route::post('/', 'create');
                Route::get('/{hobby}', 'getByID');
                Route::put('/{hobby}', 'update');
                Route::delete('/{hobby}', 'delete');
            });
        });
    });

    Route::controller(HobbyController::class)->prefix('hobbies')->group(function () {
        Route::get('/', 'list');
        Route::post('/', 'create');
        Route::get('/{hobby}', 'getByID');
        Route::put('/{hobby}', 'update');
        Route::delete('/{hobby}', 'delete');
    });
});
