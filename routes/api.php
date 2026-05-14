<?php

use App\Http\Controllers\API\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// Student API routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('students', StudentController::class);
});
