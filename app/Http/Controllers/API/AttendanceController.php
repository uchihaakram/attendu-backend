<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // ─────────────────────────────
    // STORE ATTENDANCE ← بيجي من الـ AI
    // ─────────────────────────────
    public function storeAttendance(Request $request): JsonResponse
    {
        $data      = $request->input('attendance_data');
        $sessionId = $request->input('session_schedule_id');

        if (!$data || !$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات غير مكتملة',
            ], 422);
        }

        DB::transaction(function () use ($data, $sessionId) {

            foreach ($data['present_students'] as $record) {
                $this->createAttendance($record, $sessionId, 'present');
            }

            foreach ($data['late_students'] as $record) {
                $this->createAttendance($record, $sessionId, 'late');
            }

            foreach ($data['absent_students'] as $record) {
                $this->createAttendance($record, $sessionId, 'absent');
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الحضور بنجاح',
            'data'    => [
                'session_schedule_id' => $sessionId,
                'summary'    => $data['summary'],
            ],
        ]);
    }

    // ─────────────────────────────
    // HELPER
    // ─────────────────────────────
    private function createAttendance(array $record, int $sessionId, string $status): void
    {
        $student = Student::where('student_code', $record['student_code'])->first();

        if (!$student) return;

        Attendance::create([
            'student_id'          => $student->id,
            'session_schedule_id' => $sessionId,
            'status'              => $status,
            'check_in_time'       => $status !== 'absent' ? now() : null,
            'detection_method'    => 'face_recognition',
            'confidence_score'    => $record['confidence_score'] ?? null,
        ]);
    }
}
