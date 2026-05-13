<?php

namespace App\Http\Controllers\Api\AI_Model;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Http;

class StudentFaceEnrollController extends Controller
{

    public function enroll(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);
        $student = Student::findOrFail($request->student_id);
        $fullPath = storage_path('app/public/' . $student->face_image);
        if (!file_exists($fullPath)) {
            return response()->json([
                'status' => false,
                'message' => 'Image not found on server'
            ], 404);
        }
        $aiurl = env('AI_SERVICE_URL') . '/upload-image';
        // $aiurl = url('/api/mock/enroll');
        $response = $response = Http::timeout(30)->withHeaders([
        'X-API-KEY' => env('AI_API_KEY')
    ])->attach(
            'file', // ⚠️ خليها image (الأشهر)
            file_get_contents($fullPath),
            basename($fullPath)
        )->post($aiurl, [
            'student_code' => $student->student_code,
        ]);

        if (!$response->successful()) {

            return response()->json([
                'status' => false,
                'ai_status' => $response->status(),
                'ai_response' => $response->body(),
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Enrolled successfully in AI system'
        ]);
    }
}
