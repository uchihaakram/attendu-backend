<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendancePolicyRequest\StoreAttendancePolicyRequest;
use App\Http\Requests\AttendancePolicyRequest\UpdateAttendancePolicyRequest;
use App\Models\AttendancePolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class AttendancePolicyController extends Controller
{
    // ─────────────────────────────
    // GET ALL
    // ─────────────────────────────
    public function index(): JsonResponse
    {
        $policies = AttendancePolicy::with('course')->get()
            ->map(fn($policy) => [
                'id'                   => $policy->id,
                'course_id'            => $policy->course_id,
                'course_name'          => $policy->course?->course_name,
                'max_absences_allowed' => $policy->max_absences_allowed,
                'min_attend'           => $policy->min_attend,
                'max_attend'           => $policy->max_attend,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $policies,
        ]);
    }

    // ─────────────────────────────
    // STORE
    // ─────────────────────────────
    public function store(StoreAttendancePolicyRequest $request): JsonResponse
    {
        $policy = AttendancePolicy::create($request->only([
            'course_id',
            'max_absences_allowed',
            'min_attend',
            'max_attend',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء سياسة الحضور بنجاح',
            'data'    => [
                'id'                   => $policy->id,
                'course_id'            => $policy->course_id,
                'course_name'          => $policy->course?->course_name,
                'max_absences_allowed' => $policy->max_absences_allowed,
                'min_attend'           => $policy->min_attend,
                'max_attend'           => $policy->max_attend,
            ],
        ], 201);
    }

    // ─────────────────────────────
    // SHOW
    // ─────────────────────────────
    public function show(int $id): JsonResponse
    {
        $policy = AttendancePolicy::with('course')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                   => $policy->id,
                'course_id'            => $policy->course_id,
                'course_name'          => $policy->course?->course_name,
                'max_absences_allowed' => $policy->max_absences_allowed,
                'min_attend'           => $policy->min_attend,
                'max_attend'           => $policy->max_attend,
            ],
        ]);
    }

    // ─────────────────────────────
    // UPDATE
    // ─────────────────────────────
    public function update(UpdateAttendancePolicyRequest $request, int $id): JsonResponse
    {
        $policy = AttendancePolicy::findOrFail($id);
        $policy->update($request->only([
            'max_absences_allowed',
            'min_attend',
            'max_attend',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل سياسة الحضور بنجاح',
            'data'    => [
                'id'                   => $policy->id,
                'course_id'            => $policy->course_id,
                'course_name'          => $policy->course?->course_name,
                'max_absences_allowed' => $policy->max_absences_allowed,
                'min_attend'           => $policy->min_attend,
                'max_attend'           => $policy->max_attend,
            ],
        ]);
    }

    // ─────────────────────────────
    // DELETE
    // ─────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $policy = AttendancePolicy::findOrFail($id);
        $policy->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف سياسة الحضور بنجاح',
        ]);
    }
}
