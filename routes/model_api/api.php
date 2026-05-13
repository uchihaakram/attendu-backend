<?php

use App\Http\Controllers\Api\StudentFaceController;
use Illuminate\Support\Facades\Route;

Route::post('/students/{id}/face', [StudentFaceController::class, 'store']);
