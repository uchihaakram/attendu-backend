<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Http\Requests\SessionRequests\StartSessionRequest;
use App\Http\Requests\SessionRequests\UpdateSessionRequest;
use App\Models\Session;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function __construct(private AIService $aiService) {}

    // ─────────────────────────────
    // GET ALL SESSIONS
    // ─────────────────────────────
    public function getSessions(): JsonResponse
    {
        $sessions = Session::with(['course', 'group', 'SessionInstructor'])
            ->get()
            ->map(function ($session) {
                /** @var Session $session */  // ← ضيف السطر ده

                return $this->formatSession($session);
            });

        return response()->json([
            'success' => true,
            'data'    => $sessions,
        ]);
    }

    // ─────────────────────────────
    // UPDATE SESSION
    // ─────────────────────────────
    public function update(UpdateSessionRequest $request, int $id): JsonResponse
    {
        $session = Session::findOrFail($id);

        DB::transaction(function () use ($request, $session) {

            $session->update($request->only([
                'course_id',
                'session_type',
                'day',
                'start_time',
                'end_time',
                'location',
                'group_id',
            ]));

            if ($request->has('instructor_ids')) {
                $session->instructors()->sync($request->instructor_ids);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل الجلسة بنجاح',
            'data'    => $this->formatSession(
                $session->fresh(['course', 'group', 'instructors'])
            ),
        ]);
    }

    // ─────────────────────────────
    // DELETE SESSION
    // ─────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $session = Session::findOrFail($id);
        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الجلسة بنجاح',
        ]);
    }

    // ─────────────────────────────
    // START SESSION → بيبعت للـ AI
    // ─────────────────────────────
    public function startSession(StartSessionRequest $request): JsonResponse
    {
        $session = Session::with('attendancePolicy')
            ->findOrFail($request->session_schedule_id);

        $policy = $session->attendancePolicy;

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد سياسة حضور لهذا الكورس',
            ], 422);
        }

        $payload = [
            'session_id'  => $session->id,
            'students'    => $request->students,
            'min_attend'  => $policy->min_attend,
            'max_attend'  => $policy->max_attend,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
        ];

        $aiResponse = $this->aiService->startSession($payload);

        if (!$aiResponse) {
            return response()->json([
                'success' => false,
                'message' => 'فشل الاتصال بنظام التعرف على الوجه',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم بدء السيشن بنجاح وتم إرسال البيانات للنظام',
            'data'    => [
                'session_id' => $session->id,
            ],
        ]);
    }

    // ─────────────────────────────
    // STORE ATTENDANCE ← بيجي من الـ AI
    // ─────────────────────────────
    public function storeAttendance(Request $request): JsonResponse
    {
        $data      = $request->input('attendance_data');
        $sessionId = $request->input('session_id');

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
                'session_id' => $sessionId,
                'summary'    => $data['summary'],
            ],
        ]);
    }

    // ─────────────────────────────
    // PRIVATE HELPERS
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

    private function formatSession(Session $session): array
    {
        return [
            'id'           => $session->id,
            'session_type' => $session->session_type,
            'session_date' => $session->session_date?->format('Y-m-d'),
            'start_time'   => $session->start_time,
            'end_time'     => $session->end_time,
            'location'     => $session->location,
            'day'          => $session->day,
            'status'       => $session->status,
            'group_name'   => $session->group?->name,
            'course_name'  => $session->course?->course_name,
            'instructors'  => $session->instructors->pluck('name'),
        ];
    }
}
