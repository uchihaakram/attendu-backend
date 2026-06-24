<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendancePolicy;
use App\Models\CourseEnrollment;
use App\Models\Session;
use App\Models\Warning;
use Illuminate\Support\Facades\Log;

class WarningService
{
    /**
     * يتشغل بعد كل حفظ حضور
     * يفحص الطالب ولو تجاوز الغيابات يعمل warning تلقائي
     */
    public function checkAndWarn(int $studentId, int $sessionScheduleId): void
    {
        try {
            // جيب الكورس من السيشن
            $session = Session::find($sessionScheduleId);
            if (!$session) return;

            $courseId = $session->course_id;
            $groupId  = $session->group_id;

            // تأكد إن الطالب مسجل في الكورس ده
            $enrolled = CourseEnrollment::where('student_id', $studentId)
                ->where('course_id', $courseId)
                ->where('group_id', $groupId)
                ->exists();

            if (!$enrolled) return;

            // جيب الـ policy للكورس ده
            $policy = AttendancePolicy::where('course_id', $courseId)->first();
            if (!$policy) return;

            $maxAbsences = $policy->max_absences_allowed;

            // احسب غيابات الطالب في الكورس ده
            $sessionIds = Session::where('course_id', $courseId)
                ->where('group_id', $groupId)
                ->pluck('id');

            $absentCount = Attendance::where('student_id', $studentId)
                ->whereIn('session_schedule_id', $sessionIds)
                ->where('status', 'absent')
                ->count();

            // لو الغيابات أقل من أو تساوي الحد → مفيش تحذير
            if ($absentCount <= $maxAbsences) return;

            // تحديد نوع التحذير بناءً على عدد التحذيرات السابقة
            $warningsCount = Warning::where('student_id', $studentId)
                ->where('course_id', $courseId)
                ->count();

            // لو عنده بالفعل final_warning → مفيش داعي نضيف أكتر
            $hasFinal = Warning::where('student_id', $studentId)
                ->where('course_id', $courseId)
                ->where('warning_type', 'final_warning')
                ->exists();

            if ($hasFinal) return;

            $warningType = match(true) {
                $warningsCount === 0 => 'first_warning',
                $warningsCount === 1 => 'second_warning',
                default              => 'final_warning',
            };

            $reason = "تجاوز الحد المسموح به من الغيابات ({$absentCount} غياب من أصل {$maxAbsences} مسموح)";

            Warning::create([
                'student_id'     => $studentId,
                'course_id'      => $courseId,
                'warning_type'   => $warningType,
                'warning_reason' => $reason,
                'status'         => 'active',
            ]);

            Log::info("Warning created automatically", [
                'student_id'   => $studentId,
                'course_id'    => $courseId,
                'warning_type' => $warningType,
                'absent_count' => $absentCount,
            ]);

        } catch (\Exception $e) {
            Log::error("WarningService error: " . $e->getMessage());
        }
    }
}
