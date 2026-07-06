<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequests\StoreGroupRequest;
use App\Http\Requests\GroupRequests\UpdateGroupRequest;
use App\Models\Group;
use Illuminate\Http\JsonResponse;

class GroupController extends Controller
{
    // ─────────────────────────────
    // GET ALL
    // ─────────────────────────────
    public function index(): JsonResponse
    {
        $groups = Group::with('courses')
            ->get()
            ->map(fn($group) => $this->formatGroup($group));

        return response()->json([
            'success' => true,
            'data'    => $groups,
        ]);
    }

    // ─────────────────────────────
    // STORE
    // ─────────────────────────────
    public function store(StoreGroupRequest $request): JsonResponse
    {
        $data = $request->validated();

        $group = Group::create($data);

        // ربط الكورسات (many-to-many)
        if (isset($data['course_ids'])) {
            $group->courses()->sync($data['course_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الفرقة بنجاح',
            'data'    => $this->formatGroup($group->load('courses')),
        ], 201);
    }

    // ─────────────────────────────
    // SHOW
    // ─────────────────────────────
    public function show(int $id): JsonResponse
    {
        $group = Group::with('courses')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatGroup($group),
        ]);
    }

    // ─────────────────────────────
    // UPDATE
    // ─────────────────────────────
    public function update(UpdateGroupRequest $request, int $id): JsonResponse
    {
        $group = Group::findOrFail($id);

        $data = $request->validated();

        $group->update($data);

        // تحديث العلاقات فقط لو موجودة
        if (isset($data['course_ids'])) {
            $group->courses()->sync($data['course_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل الفرقة بنجاح',
            'data'    => $this->formatGroup($group->fresh('courses')),
        ]);
    }

    // ─────────────────────────────
    // DELETE
    // ─────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $group = Group::findOrFail($id);
        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الفرقة بنجاح',
        ]);
    }

    // ─────────────────────────────
    // FORMAT RESPONSE
    // ─────────────────────────────
    private function formatGroup(Group $group): array
    {
        return [
            'id'            => $group->id,
            'group_name'    => $group->group_name,
            'group_code'    => $group->group_code,
            'academic_year' => $group->academic_year,

            // Many-to-Many courses
            'courses' => $group->courses->map(fn($course) => [
                'id'          => $course->id,
                'course_name' => $course->course_name,
                'course_code' => $course->course_code,
            ]),
        ];
    }
}
