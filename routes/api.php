<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Middleware\Authentication;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerSourceController;
use App\Http\Controllers\Api\AuthController;

Route::prefix('authentication')->controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('verify', 'verify');
});

Route::middleware(Authentication::class)->controller(DashboardController::class)->group(function () {
    Route::get('dashboard', 'index');
    Route::get('profile', fn() => response('<h1>User Profile</h1>', 200));
    Route::get('settings', fn() => response('<h1>User Settings</h1>', 200));
});







Route::apiResource('customer-sources', CustomerSourceController::class)
    ->middleware(EnsureTokenIsValid::class);

// --> using a cutome method getAll() instad of index() for GET /customer-sources
// Route::get('customer-sources', [CustomerSourceController::class, 'getAll'])
//     ->middleware(EnsureTokenIsValid::class)
//     ->name('customer-sources.getAll');
// // Define other resource routes, excluding "index"
// Route::apiResource('customer-sources', CustomerSourceController::class)
//     ->except(['index'])
//     ->middleware(EnsureTokenIsValid::class);

// --> Adding Middlewware in all routes except index()
// Route::get('customer-sources', [CustomerSourceController::class, 'index'])
//     ->name('customer-sources.index');
// // Apply middleware to all other resource routes
// Route::apiResource('customer-sources', CustomerSourceController::class)
//     ->except(['index']) // Exclude the index route
//     ->middleware(EnsureTokenIsValid::class);