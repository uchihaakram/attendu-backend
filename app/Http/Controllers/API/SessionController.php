<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequests\StoreSessionRequest;
use App\Http\Requests\SessionRequests\StartSessionRequest;
use App\Http\Requests\SessionRequests\UpdateSessionRequest;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function __construct(private AIService $aiService) {}

    // ─────────────────────────────
    // GET ALL SESSIONS
    // ─────────────────────────────
    public function getSessions(): JsonResponse
    {
        $sessions = Session::with(['course', 'group', 'instructors'])->get();

        return response()->json([
            'success' => true,
            'data'    => SessionResource::collection($sessions),
        ]);
    }

    // ─────────────────────────────
    // STORE SESSION
    // ─────────────────────────────
    public function store(StoreSessionRequest $request): JsonResponse
    {
        $session = DB::transaction(function () use ($request) {

            $session = Session::create([
                'course_id'    => $request->course_id,
                'group_id'     => $request->group_id,
                'session_type' => $request->session_type,
                'session_date' => $request->session_date,
                'day'          => $request->day,
                'start_time'   => $request->start_time,
                'end_time'     => $request->end_time,
                'location'     => $request->location,
                'status'       => 'scheduled',
            ]);

            $session->instructors()->attach($request->instructor_ids);

            return $session;
        });

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الجلسة بنجاح',
            'data'    => new SessionResource(
                $session->load(['course', 'group', 'instructors'])
            ),
        ], 201);
    }

    // ─────────────────────────────
    // START SESSION → بيبعت للـ AI
    // ─────────────────────────────
    public function startSession(StartSessionRequest $request): JsonResponse
    {
        $session = Session::with(['attendancePolicy', 'group.students'])
            ->findOrFail($request->session_schedule_id);

        $policy = $session->attendancePolicy;

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد سياسة حضور لهذا الكورس',
            ], 422);
        }

        $students = $session->group->students->map(fn($student) => [
            'student_code' => $student->student_code,
            'student_name' => $student->first_name . ' ' . $student->last_name,
        ]);

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد طلاب مسجلين في هذه الفرقة',
            ], 422);
        }

        $payload = [
            'session_schedule_id' => (string) $session->id,  // ← string مش integer
            'students'            => $students,
            'min_attend'          => $policy->min_attend,
            'max_attend'          => $policy->max_attend,
            'start_time' => \Carbon\Carbon::parse($request->start_time)->format('Y-m-d\TH:i:s'),
            'end_time'   => \Carbon\Carbon::parse($request->end_time)->format('Y-m-d\TH:i:s'),   // ← ISO 8601
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
                'session_id'     => $session->id,
                'total_students' => $students->count(),
            ],
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
            'data'    => new SessionResource(
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
}
