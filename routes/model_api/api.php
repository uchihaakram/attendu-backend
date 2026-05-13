<?php

use App\Http\Controllers\Api\model\StudentFaceController;
use Illuminate\Support\Facades\Route;

Route::post('/students/{id}/face', [StudentFaceController::class, 'store']);
