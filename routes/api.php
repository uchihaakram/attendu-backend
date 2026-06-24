<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AttendancePolicyController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\GroupController;
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
// Session API routes
Route::middleware(['auth:sanctum', 'json.unicode'])->group(function () {

    // admin + instructor
    Route::middleware('role:admin,instructor')->group(function () {
        Route::get('/sessions',        [SessionController::class, 'getSessions']);
        Route::post('/sessions/start', [SessionController::class, 'startSession']);
    });

    // admin only
    Route::middleware('role:admin')->group(function () {
        Route::post('/sessions',          [SessionController::class, 'store']);
        Route::put('/sessions/{id}',      [SessionController::class, 'update']);
        Route::delete('/sessions/{id}',   [SessionController::class, 'destroy']);
    });
});
// ── AI only (X-API-KEY, بدون Sanctum) ──
Route::middleware(['ai.key','json.unicode'])->group(function () {
    Route::post('/attendance/store', [AttendanceController::class, 'storeAttendance']);
});

// ── Admin + Instructor ──
Route::middleware(['auth:sanctum', 'role:admin,instructor', 'json.unicode'])->group(function () {
    Route::get('/attendance/session/{sessionId}', [AttendanceController::class, 'getAttendanceBySession']);
    Route::get('/attendance/student/{studentId}', [AttendanceController::class, 'getAttendanceByStudent']);
});

// ── Admin only ──
Route::middleware(['auth:sanctum', 'role:admin', 'json.unicode'])->group(function () {
    Route::put('/attendance/{id}', [AttendanceController::class, 'updateAttendance']);
});
Route::middleware(['auth:sanctum', 'role:admin', 'json.unicode'])->group(function () {
    Route::get('/attendance-policies',      [AttendancePolicyController::class, 'index']);
    Route::post('/attendance-policies',     [AttendancePolicyController::class, 'store']);
    Route::get('/attendance-policies/{id}', [AttendancePolicyController::class, 'show']);
    Route::put('/attendance-policies/{id}', [AttendancePolicyController::class, 'update']);
    Route::delete('/attendance-policies/{id}', [AttendancePolicyController::class, 'destroy']);
});
Route::middleware(['auth:sanctum', 'role:admin', 'json.unicode'])->group(function () {
    Route::get('/courses',          [CourseController::class, 'index']);
    Route::post('/courses',         [CourseController::class, 'store']);
    Route::get('/courses/{id}',     [CourseController::class, 'show']);
    Route::put('/courses/{id}',     [CourseController::class, 'update']);
    Route::delete('/courses/{id}',  [CourseController::class, 'destroy']);
});
Route::middleware(['auth:sanctum', 'role:admin', 'json.unicode'])->group(function () {
    Route::get('/groups',          [GroupController::class, 'index']);
    Route::post('/groups',         [GroupController::class, 'store']);
    Route::get('/groups/{id}',     [GroupController::class, 'show']);
    Route::put('/groups/{id}',     [GroupController::class, 'update']);
    Route::delete('/groups/{id}',  [GroupController::class, 'destroy']);
});
