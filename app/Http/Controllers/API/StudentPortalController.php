<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CourseEnrollment;
use App\Models\Session;
use App\Models\Student;
use App\Models\Warning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    private function studentId(Request $request): int
    {
        $studentId = $request->user()->student_id;
        abort_if(!$studentId, 403, 'هذا الحساب غير مرتبط بطالب');
        return $studentId;
    }

    // الجلسات الخاصة بالطالب
    public function sessions(Request $request): JsonResponse
    {
        $studentId = $this->studentId($request);

        $groupIds = CourseEnrollment::where('student_id', $studentId)
            ->pluck('group_id')->unique();

        $sessions = Session::with(['course', 'group', 'instructors'])
            ->whereIn('group_id', $groupIds)
            ->orderByDesc('session_date')
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id,
                'course_name'  => $s->course?->course_name,
                'session_type' => $s->session_type,
                'session_date' => $s->session_date?->format('Y-m-d'),
                'day'          => $s->day,
                'location'     => $s->location,
                'status'       => $s->status,
                'instructors'  => $s->instructors->pluck('name'),
            ]);

        return response()->json(['success' => true, 'data' => $sessions]);
    }

    // إحصائيات الحضور والغياب (بنفس منطق AttendanceController لكن مبسط)
    public function attendance(Request $request): JsonResponse
    {
        $studentId = $this->studentId($request);

        $attendances = Attendance::with('session.course')
            ->where('student_id', $studentId)
            ->get()
            ->map(fn($att) => [
                'course_name'   => $att->session?->course?->course_name,
                'session_type'  => $att->session?->session_type,
                'session_date'  => $att->session?->session_date?->format('Y-m-d'),
                'status'        => $att->status,
                'check_in_time' => $att->check_in_time,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'attendances' => $attendances,
                'summary' => [
                    'total'   => $attendances->count(),
                    'present' => $attendances->where('status', 'present')->count(),
                    'late'    => $attendances->where('status', 'late')->count(),
                    'absent'  => $attendances->where('status', 'absent')->count(),
                ],
            ],
        ]);
    }

    // التحذيرات الخاصة بالطالب
    public function warnings(Request $request): JsonResponse
    {
        $studentId = $this->studentId($request);

        $warnings = Warning::with('course')
            ->where('student_id', $studentId)
            ->latest()
            ->get()
            ->map(fn($w) => [
                'course_name'      => $w->course?->course_name,
                'warning_type'     => $w->warning_type,
                'warning_type_label' => $w->warning_type_label,
                'warning_reason'   => $w->warning_reason,
                'status'           => $w->status,
                'status_label'     => $w->status_label,
                'created_at'       => $w->created_at->format('Y-m-d'),
            ]);

        return response()->json(['success' => true, 'data' => $warnings]);
    }
    // app/Http/Controllers/API/Auth/StudentAuthController.php

public function profile(Request $request): JsonResponse
{
    $student = Student::with('groups.courses')
        ->findOrFail($request->user()->student_id);

    return response()->json([
        'status' => true,
        'data'   => [
            'id'           => $student->id,
            'first_name'   => $student->first_name,
            'last_name'    => $student->last_name,
            'student_code' => $student->student_code,
            'email'        => $student->email,
            'phone_number' => $student->phone_number,
            'gender'       => $student->gender,
            'national_id'  => $student->national_id,
            'face_image'   => $student->face_image
                ? asset('storage/' . $student->face_image)
                : null,
            'groups'       => $student->groups->map(fn($group) => [
                'group_name'    => $group->group_name,
                'courses'       => $group->courses->pluck('course_name'),
                'academic_year' => $group->academic_year,
            ]),
        ],
    ]);
}
}
