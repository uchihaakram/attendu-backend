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
        $groups = Group::with('course')->get()
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
        $group = Group::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الفرقة بنجاح',
            'data'    => $this->formatGroup($group->load('course')),
        ], 201);
    }

    // ─────────────────────────────
    // SHOW
    // ─────────────────────────────
    public function show(int $id): JsonResponse
    {
        $group = Group::with('course')->findOrFail($id);

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
        $group->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل الفرقة بنجاح',
            'data'    => $this->formatGroup($group->fresh('course')),
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
    // HELPER
    // ─────────────────────────────
    private function formatGroup(Group $group): array
    {
        return [
            'id'            => $group->id,
            'group_name'    => $group->group_name,
            'group_code'    => $group->group_code,
            'academic_year' => $group->academic_year,
            'course_id'     => $group->course_id,
            'course_name'   => $group->course?->course_name,
        ];
    }
}
