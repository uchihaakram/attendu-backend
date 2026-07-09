<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequests\StoreSessionRequest;
use App\Http\Requests\SessionRequests\StartSessionRequest;
use App\Http\Requests\SessionRequests\UpdateSessionRequest;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use App\Services\AIService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function __construct(private AIService $aiService) {}

    // ─────────────────────────────
    // GET ALL SESSIONS
    // ─────────────────────────────
    public function getSessions(Request $request): JsonResponse
{
    $query = Session::with(['course', 'group', 'instructors']);

    if ($request->user()->role !== 'admin') {
        $query->whereHas('instructors', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        });
    }

    if ($request->filled('search')) {
        $query->whereHas('instructors', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('session_type')) {
        $query->where('session_type', $request->session_type);
    }

    if ($request->filled('day')) {
        $query->where('day', $request->day);
    }

    return response()->json([
        'success' => true,
        'data'    => $query->get(),
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
                'session_date' => Carbon::parse($request->session_date)->utc()->toDateString(),
                'day'          => $request->day,
                'start_time'   => Carbon::parse($request->start_time)->utc(),
                'end_time'     => Carbon::parse($request->end_time)->utc(),
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
    // START SESSION
    // ─────────────────────────────
    public function startSession(StartSessionRequest $request): JsonResponse
    {
        $session = Session::with(['attendancePolicy', 'group.students'])
            ->findOrFail($request->session_schedule_id);
        $this->authorize('start', $session);

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
        $duration = $session->start_time->diffInMinutes($session->end_time);
        $payload = [
            'session_schedule_id' => (string) $session->id,
            'students'            => $students,
            'min_attend'          => $policy->min_attend,
            'max_attend'          => $policy->max_attend,
            'start_time' =>now('UTC') ->toIso8601String(),
            'end_time'   =>now('UTC')->addMinutes($duration)->toIso8601String(),
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
                'group_id',
                'session_type',
                'day',
                'start_time',
                'end_time',
                'location',
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

    // ─────────────────────────────
    // LIVE SESSION
    // ─────────────────────────────
    public function liveSession(int $id): JsonResponse
    {
        $session = Session::with(['course', 'group', 'instructors'])
            ->findOrFail($id);

        $this->authorize('view', $session);   // ← ضيف السطر ده

        $students = $session->group->students()->get();

        $attendances = \App\Models\Attendance::where('session_schedule_id', $id)
            ->get()
            ->keyBy('student_id');

        $studentsList = $students->map(function ($student) use ($attendances) {

            $attendance = $attendances->get($student->id);

            return [
                'student_id'        => $student->id,
                'student_name'      => $student->first_name . ' ' . $student->last_name,
                'student_code'      => $student->student_code,
                'enrollment_status' => 'مسجل',
                'attendance_status' => $attendance?->status ?? 'لم يسجل بعد',
                'confidence_score'  => $attendance?->confidence_score
                    ? $attendance->confidence_score . '%'
                    : null,
                'check_in_time'     => $attendance?->check_in_time
                    ? Carbon::parse($attendance->check_in_time)->timezone('Africa/Cairo')->format('H:i:s')
                    : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'session_id'   => $session->id,
                'course_name'  => $session->course?->course_name,
                'session_type' => $session->session_type,
                'group_name'   => $session->group?->group_name,
                'start_time'   => $session->start_time
                    ? Carbon::parse($session->start_time)->timezone('Africa/Cairo')->format('H:i')
                    : null,
                'end_time'     => $session->end_time
                    ? Carbon::parse($session->end_time)->timezone('Africa/Cairo')->format('H:i')
                    : null,
                'status'       => $session->status,
                'instructors'  => $session->instructors->pluck('name'),
                'students'     => $studentsList,
                'summary'      => [
                    'total'        => $studentsList->count(),
                    'present'      => $studentsList->where('attendance_status', 'present')->count(),
                    'late'         => $studentsList->where('attendance_status', 'late')->count(),
                    'absent'       => $studentsList->where('attendance_status', 'absent')->count(),
                    'not_recorded' => $studentsList->where('attendance_status', 'لم يسجل بعد')->count(),
                ],
            ],
        ]);
    }
}
