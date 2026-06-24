<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Session;
use App\Http\Requests\AttendanceRequests\StoreAttendanceRequest;
use App\Http\Requests\AttendanceRequests\UpdateAttendanceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    // ─────────────────────────────
    // STORE ATTENDANCE ← بيجي من الـ AI
    // ─────────────────────────────
    public function storeAttendance(StoreAttendanceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data      = $validated['attendance_data'];
        $sessionId = (int) $validated['session_schedule_id']; // ← cast لـ integer لو جه string من الـ AI

        $notFound = [];

        try {
            DB::transaction(function () use ($data, $sessionId, &$notFound) {
                foreach ($data['present_students'] as $record) {
                    if (!$this->createAttendance($record, $sessionId, 'present'))
                        $notFound[] = $record['student_code'];
                }
                foreach ($data['late_students'] as $record) {
                    if (!$this->createAttendance($record, $sessionId, 'late'))
                        $notFound[] = $record['student_code'];
                }
                foreach ($data['absent_students'] as $record) {
                    if (!$this->createAttendance($record, $sessionId, 'absent'))
                        $notFound[] = $record['student_code'];
                }
            });
        } catch (\Exception $e) {
            Log::error('storeAttendance transaction failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'فشل تسجيل الحضور',
            ], 500);
        }

        if (!empty($notFound)) {
            Log::warning('storeAttendance: student_codes not found in DB', [
                'session_schedule_id' => $sessionId,
                'missing_codes'       => $notFound,
            ]);
        }

        return response()->json([
            'success'          => true,
            'message'          => 'تم تسجيل الحضور بنجاح',
            'data'             => [
                'session_schedule_id' => $sessionId,
                'summary'             => $data['summary'],
                'not_found_codes'     => $notFound,
            ],
        ]);
    }

    // ─────────────────────────────
    // GET ATTENDANCE BY SESSION
    // ─────────────────────────────
    public function getAttendanceBySession(int $sessionId): JsonResponse
    {
        $session = Session::findOrFail($sessionId);

        $attendances = Attendance::with('student')
            ->where('session_schedule_id', $sessionId)
            ->get()
            ->map(fn($att) => [
                'attendance_id'    => $att->id,
                'student_id'       => $att->student_id,
                'student_code'     => $att->student?->student_code,
                'student_name'     => trim(($att->student?->first_name ?? '') . ' ' . ($att->student?->last_name ?? '')),
                'status'           => $att->status,
                'check_in_time'    => $att->check_in_time,
                'detection_method' => $att->detection_method,
                'confidence_score' => $att->confidence_score,
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'session_id'  => $session->id,
                'attendances' => $attendances,
                'summary'     => [
                    'total'   => $attendances->count(),
                    'present' => $attendances->where('status', 'present')->count(),
                    'late'    => $attendances->where('status', 'late')->count(),
                    'absent'  => $attendances->where('status', 'absent')->count(),
                ],
            ],
        ]);
    }

    // ─────────────────────────────
    // GET ATTENDANCE BY STUDENT
    // ─────────────────────────────
    public function getAttendanceByStudent(int $studentId): JsonResponse
    {
        $student = Student::findOrFail($studentId);

        // الترتيب على session_date مش check_in_time عشان الغائبين check_in_time = null
        $attendances = Attendance::with('session.course')
            ->where('student_id', $studentId)
            ->join('sessionschedules', 'attendances.session_schedule_id', '=', 'sessionschedules.id')
            ->orderByDesc('sessionschedules.session_date')
            ->select('attendances.*')
            ->get()
            ->map(fn($att) => [
                'attendance_id' => $att->id,
                'session_id'    => $att->session_schedule_id,
                'course_name'   => $att->session?->course?->course_name,
                'session_type'  => $att->session?->session_type,
                'session_date'  => $att->session?->session_date?->format('Y-m-d'),
                'status'        => $att->status,
                'check_in_time' => $att->check_in_time,
                'confidence_score' => $att->confidence_score,
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'student_id'   => $student->id,
                'student_name' => trim($student->first_name . ' ' . $student->last_name),
                'attendances'  => $attendances,
                'summary'      => [
                    'total'   => $attendances->count(),
                    'present' => $attendances->where('status', 'present')->count(),
                    'late'    => $attendances->where('status', 'late')->count(),
                    'absent'  => $attendances->where('status', 'absent')->count(),
                ],
            ],
        ]);
    }

    // ─────────────────────────────
    // UPDATE ATTENDANCE (manual override)
    // ─────────────────────────────
    public function updateAttendance(UpdateAttendanceRequest $request, int $id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'status' => $request->status,

            // لو الـ request فيه check_in_time صريح → استخدمه
            // لو status = absent → null
            // لو status = present/late ومفيش check_in_time في الـ request → خلي القيمة الموجودة زي ما هي
            'check_in_time' => $request->status === 'absent'
                ? null
                : ($request->check_in_time ?? $attendance->check_in_time),

            'detection_method' => 'manual',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل الحضور بنجاح',
            'data'    => $attendance->fresh(),
        ]);
    }

    // ─────────────────────────────
    // HELPER
    // ─────────────────────────────

    /**
     * يعمل attendance record للطالب في السيشن.
     * لو السجل موجود من قبل يعمل update (منع الـ duplicate).
     * بيرجع true لو الطالب اتلقى، false لو مش موجود في DB.
     */
    private function createAttendance(array $record, int $sessionId, string $status): bool
    {
        $student = Student::where('student_code', $record['student_code'])->first();

        if (!$student) return false;

        Attendance::updateOrCreate(
            // شرط التطابق: نفس الطالب + نفس السيشن
            [
                'student_id'          => $student->id,
                'session_schedule_id' => $sessionId,
            ],
            // القيم اللي بنحدثها أو بنكتبها
            [
                'status'           => $status,
                'check_in_time'    => $status !== 'absent'
                    ? ($record['check_in_time'] ?? now())
                    : null,
                'detection_method' => 'face_recognition',
                'confidence_score' => $record['confidence_score'] ?? null,
            ]
        );

        return true;
    }
}
