<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// Authentication Routes
//register
Route::post('/register', [App\Http\Controllers\API\Auth\AuthController::class, 'register']);
//login
Route::post('/login', [App\Http\Controllers\API\Auth\AuthController::class, 'login']);
//logout and me routes are protected by sanctum middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\API\Auth\AuthController::class, 'logout']);
    Route::get('/me', [App\Http\Controllers\API\Auth\AuthController::class, 'me']);
});
