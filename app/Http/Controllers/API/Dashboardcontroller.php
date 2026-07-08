<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Session;
use App\Models\Attendance;
use App\Models\Warning;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/stats
     */
    public function stats(): JsonResponse
    {
        // إجمالي عدد الطلاب
        $totalStudents = Student::count();

        // إجمالي عدد الجلسات
        $totalSessions = Session::count();

        // عدد الجلسات اليوم
        $sessionsToday = Session::whereDate('session_date', today())->count();

        // عدد الجلسات النشطة
        $activeSessions = Session::where('status', 'active')->count();

        // عدد التحذيرات النشطة
        $totalWarnings = Warning::where('status', 'active')->count();

        // إحصائيات حضور اليوم
        $presentToday = Attendance::whereDate('created_at', today())
            ->where('status', 'present')
            ->count();

        $lateToday = Attendance::whereDate('created_at', today())
            ->where('status', 'late')
            ->count();

        $absentToday = Attendance::whereDate('created_at', today())
            ->where('status', 'absent')
            ->count();

        $totalAttendanceToday = Attendance::whereDate('created_at', today())->count();

        $todayAttendanceRate = $totalAttendanceToday > 0
            ? (($presentToday + $lateToday) / $totalAttendanceToday) * 100
            : 0;

        // متوسط دقة الموديل
        $avgConfidenceScore = Attendance::where('detection_method', 'face_recognition')
            ->whereNotNull('confidence_score')
            ->avg('confidence_score');

        // متوسط نسبة الحضور العام
        $avgAttendanceRate = $this->calcAvgAttendanceRate();

        // متوسط نسبة الحضور حسب نوع الجلسة
        $avgByType = $this->calcAvgAttendanceByType();

        return response()->json([
            'success' => true,
            'data' => [
                'total_students'         => $totalStudents,
                'total_sessions'         => $totalSessions,
                'sessions_today'         => $sessionsToday,
                'active_sessions'        => $activeSessions,

                'present_today'          => $presentToday,
                'late_today'             => $lateToday,
                'absent_today'           => $absentToday,

                'today_attendance_rate'  => round($todayAttendanceRate, 1),
                'avg_attendance_rate'    => round($avgAttendanceRate, 1),

                'total_warnings'         => $totalWarnings,
                'avg_confidence_score'   => round($avgConfidenceScore ?? 0, 1),

                'avg_attendance_by_type' => [
                    'lecture' => round($avgByType['lecture'] ?? 0, 1),
                    'section' => round($avgByType['section'] ?? 0, 1),
                    'lab'     => round($avgByType['lab'] ?? 0, 1),
                ],
            ],
        ]);
    }

    /**
     * حساب متوسط نسبة الحضور العام
     */
    private function calcAvgAttendanceRate(): float
    {
        $sessions = Session::withCount([
            'attendances',
            'attendances as present_count' => fn($q) => $q->where('status', 'present'),
            'attendances as late_count'    => fn($q) => $q->where('status', 'late'),
        ])->get();

        if ($sessions->isEmpty()) {
            return 0;
        }

        $rates = $sessions
            ->filter(fn($s) => $s->attendances_count > 0)
            ->map(fn($s) => (($s->present_count + $s->late_count) / $s->attendances_count) * 100);

        return $rates->isEmpty() ? 0 : $rates->avg();
    }

    /**
     * حساب متوسط نسبة الحضور حسب نوع الجلسة
     */
    private function calcAvgAttendanceByType(): array
    {
        $types = ['lecture', 'section', 'lab'];
        $result = [];

        foreach ($types as $type) {

            $sessions = Session::where('session_type', $type)
                ->withCount([
                    'attendances',
                    'attendances as present_count' => fn($q) => $q->where('status', 'present'),
                    'attendances as late_count'    => fn($q) => $q->where('status', 'late'),
                ])
                ->get();

            $rates = $sessions
                ->filter(fn($s) => $s->attendances_count > 0)
                ->map(fn($s) => (($s->present_count + $s->late_count) / $s->attendances_count) * 100);

            $result[$type] = $rates->isEmpty() ? 0 : $rates->avg();
        }

        return $result;
    }
}
