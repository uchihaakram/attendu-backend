<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\SessionController;
use App\Http\Controllers\API\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// Student API routes
Route::middleware(['auth:sanctum', 'role:admin', 'json.unicode'])->group(function () {
    Route::apiResource('students', StudentController::class);
    Route::post('students/{student}', [StudentController::class, 'update']);
});
Route::middleware(['auth:sanctum', 'role:admin', 'json.unicode'])->group(function () {
    // Sessions
    Route::get('/sessions',          [SessionController::class, 'getSessions']);
    Route::post('/sessions/start',   [SessionController::class, 'startSession']);
    Route::put('/sessions/{id}',     [SessionController::class, 'update']);
    Route::delete('/sessions/{id}',  [SessionController::class, 'destroy']);
});
Route::middleware(['auth:sanctum', 'role:admin', 'json.unicode'])->group(function () {

    // Attendance
    Route::post('/attendance/store', [AttendanceController::class, 'storeAttendance']);
});
