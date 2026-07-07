<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Attendance;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    /**
     * GET /api/reports/students
     * قائمة الطلاب مع إحصائيات الحضور
     */
    public function index(Request $request): JsonResponse
    {
        $query = CourseEnrollment::query()
            ->with([
                'student',
                'group',
                'course',
            ]);

        // فلتر باسم المقرر
        if ($request->filled('course_name')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('course_name', 'like', '%' . $request->course_name . '%');
            });
        }

        // فلتر بالفرقة الدراسية
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        // فلتر بنوع الجلسة
        if ($request->filled('session_type')) {
            $query->whereHas('course.sessions', function ($q) use ($request) {
                $q->where('session_type', $request->session_type);
            });
        }

        // فلتر باسم المحاضر / المعيد
        if ($request->filled('instructor_name')) {
            $query->whereHas('course.sessions.instructors', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->instructor_name . '%');
            });
        }

        $enrollments = $query->get();

        $data = $enrollments->map(function ($enrollment) use ($request) {
            $student  = $enrollment->student;
            $course   = $enrollment->course;
            $group    = $enrollment->group;
            if (!$course || !$group) {
                return null;
            }

            // جلب كل الجلسات اللي خاصة بالكورس والجروب ده
            $sessionsQuery = Session::where('course_id', $course->id)
                ->where('group_id', $group->id);

            if ($request->filled('session_type')) {
                $sessionsQuery->where('session_type', $request->session_type);
            }

            if ($request->filled('instructor_name')) {
                $sessionsQuery->whereHas('instructors', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->instructor_name . '%');
                });
            }

            $sessionIds = $sessionsQuery->pluck('id');

            // إحصائيات الحضور
            $attendances = Attendance::where('student_id', $student->id)
                ->whereIn('session_schedule_id', $sessionIds)
                ->get();

            $presentCount = $attendances->where('status', 'present')->count();
            $lateCount    = $attendances->where('status', 'late')->count();
            $absentCount  = $attendances->where('status', 'absent')->count();
            $totalSessions = $sessionIds->count();

            $attendanceRate = $totalSessions > 0
                ? round(($presentCount + $lateCount) / $totalSessions * 100, 1)
                : 0;

            return [
                'student_id'        => $student->id,
                'student_code'      => $student->student_code,
                'student_name'      => $student->first_name . ' ' . $student->last_name,
                'group_name'        => $group->group_name,
                'academic_year'     => $group->academic_year,
                'course_name'       => $course->course_name,
                'enrollment_status' => 'قيد نشط',
                'present_count'     => $presentCount,
                'late_count'        => $lateCount,
                'absent_count'      => $absentCount,
                'total_sessions'    => $totalSessions,
                'attendance_rate'   => $attendanceRate,
            ];
        })->filter()->values();;

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/reports/students/{id}
     * تفاصيل طالب واحد مع سجل كل الجلسات
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // جلب الطالب مع enrollments
        $enrollments = CourseEnrollment::with(['student', 'group', 'course'])
            ->where('student_id', $id)
            ->get();

        if ($enrollments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'الطالب غير موجود أو غير مسجل في أي مقرر',
            ], 404);
        }

        $student = $enrollments->first()->student;

        $coursesData = $enrollments->map(function ($enrollment) {
            $course  = $enrollment->course;
            $group   = $enrollment->group;

            $sessionIds = Session::where('course_id', $course->id)
                ->where('group_id', $group->id)
                ->pluck('id');

            $attendances = Attendance::with('session')
                ->where('student_id', $enrollment->student_id)
                ->whereIn('session_schedule_id', $sessionIds)
                ->get();

            $presentCount  = $attendances->where('status', 'present')->count();
            $lateCount     = $attendances->where('status', 'late')->count();
            $absentCount   = $attendances->where('status', 'absent')->count();
            $totalSessions = $sessionIds->count();

            $attendanceRate = $totalSessions > 0
                ? round(($presentCount + $lateCount) / $totalSessions * 100, 1)
                : 0;

            // تفاصيل كل جلسة
            $sessions = $attendances->map(function ($att) {
                return [
                    'session_date'     => optional($att->session)->session_date,
                    'session_type'     => optional($att->session)->session_type,
                    'course_name'      => optional($att->session?->course)->course_name,
                    'status'           => $att->status,
                    'check_in_time'    => $att->check_in_time,
                    'confidence_score' => $att->confidence_score,
                    'detection_method' => $att->detection_method,
                ];
            });

            return [
                'course_name'     => $course->course_name,
                'group_name'      => $group->group_name,
                'academic_year'   => $group->academic_year,
                'summary' => [
                    'present_count'   => $presentCount,
                    'late_count'      => $lateCount,
                    'absent_count'    => $absentCount,
                    'total_sessions'  => $totalSessions,
                    'attendance_rate' => $attendanceRate,
                ],
                'sessions' => $sessions,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id'           => $student->id,
                    'student_code' => $student->student_code,
                    'student_name' => $student->first_name . ' ' . $student->last_name,
                    'email'        => $student->email,
                    'phone_number' => $student->phone_number,
                    'gender'       => $student->gender,
                ],
                'courses' => $coursesData,
            ],
        ]);
    }
}
