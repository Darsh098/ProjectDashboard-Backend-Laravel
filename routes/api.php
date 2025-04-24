<?php

use App\Http\Middleware\CheckAuthorization;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerSourceController;
use App\Http\Controllers\Api\AuthController;

Route::post('signup', [AuthController::class, 'signup']);
Route::post('signin', [AuthController::class, 'signin']);
Route::post('verify', [AuthController::class, 'verify']);

Route::get('dashboard', fn() => response('<h1>Dashboard Success</h1>', 200))
    ->middleware(CheckAuthorization::class);




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