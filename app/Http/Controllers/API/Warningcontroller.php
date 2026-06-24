<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Warning;
use App\Models\Student;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WarningController extends Controller
{
    /**
     * GET /api/warnings
     * قائمة التحذيرات مع فلتر بحث باسم الطالب
     */
    public function index(Request $request): JsonResponse
    {
        $query = Warning::with(['student', 'course'])
            ->latest();

        // فلتر بحث باسم الطالب أو كود الطالب
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        $warnings = $query->get();

        $data = $warnings->map(function ($warning) {
            $student = $warning->student;
            $group   = $student->groups()->first();

            return [
                'id'               => $warning->id,
                'student_code'     => $student->student_code,
                'student_name'     => $student->first_name . ' ' . $student->last_name,
                'course_name'      => $warning->course->course_name,
                'group_name'       => $group?->group_name ?? '—',
                'academic_year'    => $group?->academic_year ?? '—',
                'enrollment_status'=> 'قيد نشط',
                'warning_type'     => $warning->warning_type,
                'warning_type_label' => $warning->warning_type_label,
                'warning_reason'   => $warning->warning_reason,
                'status'           => $warning->status,
                'status_label'     => $warning->status_label,
                'created_at'       => $warning->created_at->format('Y-m-d'),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/warnings/{studentId}
     * إرسال تحذير يدوي لطالب من صفحة التقارير
     */
    public function store(Request $request, int $studentId): JsonResponse
    {
        $request->validate([
            'course_id'      => 'required|exists:courses,id',
            'warning_reason' => 'nullable|string|max:500',
        ]);

        $student = Student::find($studentId);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'الطالب غير موجود',
            ], 404);
        }

        // التحقق إن الطالب مسجل في الكورس ده
        $enrolled = CourseEnrollment::where('student_id', $studentId)
            ->where('course_id', $request->course_id)
            ->exists();

        if (!$enrolled) {
            return response()->json([
                'success' => false,
                'message' => 'الطالب غير مسجل في هذا المقرر',
            ], 422);
        }

        // تحديد نوع التحذير بناءً على عدد التحذيرات السابقة
        $warningsCount = Warning::where('student_id', $studentId)
            ->where('course_id', $request->course_id)
            ->count();

        $warningType = match(true) {
            $warningsCount === 0 => 'first_warning',
            $warningsCount === 1 => 'second_warning',
            default              => 'final_warning',
        };

        $warning = Warning::create([
            'student_id'     => $studentId,
            'course_id'      => $request->course_id,
            'warning_type'   => $warningType,
            'warning_reason' => $request->warning_reason,
            'status'         => 'active',
        ]);

        $warning->load(['student', 'course']);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال التحذير بنجاح',
            'data' => [
                'id'                 => $warning->id,
                'student_code'       => $warning->student->student_code,
                'student_name'       => $warning->student->first_name . ' ' . $warning->student->last_name,
                'course_name'        => $warning->course->course_name,
                'warning_type'       => $warning->warning_type,
                'warning_type_label' => $warning->warning_type_label,
                'warning_reason'     => $warning->warning_reason,
                'status'             => $warning->status,
                'status_label'       => $warning->status_label,
            ],
        ], 201);
    }

    /**
     * DELETE /api/warnings/{id}
     * حذف تحذير
     */
    public function destroy(int $id): JsonResponse
    {
        $warning = Warning::find($id);

        if (!$warning) {
            return response()->json([
                'success' => false,
                'message' => 'التحذير غير موجود',
            ], 404);
        }

        $warning->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التحذير بنجاح',
        ]);
    }
}
