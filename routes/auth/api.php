<?php

use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// Authentication Routes
//login
Route::post('/login', [App\Http\Controllers\API\Auth\AuthController::class, 'login']);
//logout and me routes are protected by sanctum middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\API\Auth\AuthController::class, 'logout']);
    Route::get('/me', [App\Http\Controllers\API\Auth\AuthController::class, 'me']);
});
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    // instructors management
    Route::get('/users', [UserController::class, 'index']);

    Route::post('/users/instructor', [UserController::class, 'createInstructor']);

    Route::get('/users/{id}', [UserController::class, 'show']);

    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
