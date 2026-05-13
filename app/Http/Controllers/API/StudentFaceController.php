<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Http\Requests\StudentRequests\StudentFaceRequest;
use Illuminate\Support\Facades\Http;

class StudentFaceController extends Controller
{
    public function store(StudentFaceRequest $request, $id)
    {
        $student = Student::where('id', '=',$id,'and')->firstOrFail();
        $url = env('AI_SERVICE_URL') . '/generate-embedding';
        $face_image = $request->file('face_image');
        //  $response = Http::timeout(30)->attach(
        //     'face_image',
        //     file_get_contents($face_image->getRealPath()),
        //     $face_image->getClientOriginalName()
        // )->post($url, [
        //     'student_code' => $student->student_code
        // ]);
        // if (!$response->successful()) {

        //     return response()->json([
        //         'message' => 'AI service unavailable'
        //     ], 500);
        // }
        // $embedding = $response->json('embedding');
        return response()->json([
            'message' => 'Endpoint working successfully',
            'student_code' => $student->student_code,
            'image_name' => $face_image->getClientOriginalName()
        ], 200);
    }
}
