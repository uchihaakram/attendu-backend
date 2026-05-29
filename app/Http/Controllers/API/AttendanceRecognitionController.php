<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceRecognitionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'session_schedule_id' => 'required|exists:SessionSchedules,id',

            'recognized_students' => 'required|array',
        ]);

        foreach ($request->recognized_students as $recognized) {

            $student = Student::where(
                'student_code',
                $recognized['student_code']
            )->first();

            if (!$student) {
                continue;
            }

            // prevent duplicate attendance
            $exists = Attendance::where([
                'student_id' => $student->id,

                'SessionSchedules_id' =>
                    $request->session_schedule_id
            ])->exists();

            if ($exists) {
                continue;
            }

            Attendance::create([

                'student_id' => $student->id,

                'SessionSchedules_id' =>
                    $request->session_schedule_id,

                'status' => $recognized['status'],

                'check_in_time' =>
                    $recognized['check_in_time'],

                'detection_method' =>
                    'face_recognition',

                'confidence_score' =>
                    $recognized['confidence'] ?? null,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' =>
                'Attendance stored successfully'
        ]);
    }
}
