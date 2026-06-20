<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequests\StoreCourseRequest;
use App\Http\Requests\CourseRequests\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    // ─────────────────────────────
    // GET ALL
    // ─────────────────────────────
    public function index(): JsonResponse
    {
        $courses = Course::with('attendancePolicies')
            ->select('id', 'course_name', 'course_code', 'description', 'start_date', 'end_date')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $courses,
        ]);
    }

    // ─────────────────────────────
    // STORE
    // ─────────────────────────────
    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = Course::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء المقرر بنجاح',
            'data'    => $course,
        ], 201);
    }

    // ─────────────────────────────
    // SHOW
    // ─────────────────────────────
    public function show(int $id): JsonResponse
    {
        $course = Course::with('attendancePolicies')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $course,
        ]);
    }

    // ─────────────────────────────
    // UPDATE
    // ─────────────────────────────
    public function update(UpdateCourseRequest $request, int $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $course->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل المقرر بنجاح',
            'data'    => $course->fresh(),
        ]);
    }

    // ─────────────────────────────
    // DELETE
    // ─────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المقرر بنجاح',
        ]);
    }
}
