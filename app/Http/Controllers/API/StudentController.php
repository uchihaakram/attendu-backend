<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequests\StoreStudentRequest;
use App\Http\Requests\StudentRequests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\Group;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function __construct(private AIService $aiService) {}

    // ─────────────────────────────
    // INDEX
    // ─────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Student::with('groups.courses');

        if ($request->filled('search')) {
            $query->where('student_code', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('group_id')) {
            $query->whereHas('groups', function ($q) use ($request) {
                $q->where('groups.id', $request->group_id);
            });
        }

        $students = $query->paginate(10);

        return response()->json([
            'status'  => true,
            'message' => $students->isEmpty() ? 'عفوا لا يوجد بيانات للعرض' : null,
            'data'    => StudentResource::collection($students),
        ]);
    }

    // ─────────────────────────────
    // STORE
    // ─────────────────────────────
    public function store(StoreStudentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $groupId = $data['group_id'] ?? null;
        unset($data['group_id'], $data['course_ids']);

        $imagePath = null;

        DB::beginTransaction();

        try {
            // upload image
            if ($request->hasFile('face_image')) {
                $imagePath = $request->file('face_image')->store('students/faces', 'public');
                $data['face_image'] = $imagePath;
            }

            // create student
            $student = Student::create($data);

            // get courses from group (SOURCE OF TRUTH)
            $group = Group::with('courses')->findOrFail($groupId);

            foreach ($group->courses as $course) {
                $student->courseEnrollments()->create([
                    'group_id'    => $groupId,
                    'course_id'   => $course->id,
                    'enrolled_at' => now(),
                ]);
            }

            // AI enroll
            $enrolled = $this->aiService->enrollFace(
                $student->face_image,
                $student->student_code
            );

            if (!$enrolled) {
                DB::rollBack();

                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }

                return response()->json([
                    'status'  => false,
                    'message' => 'فشل تسجيل الوجه في نظام الذكاء الاصطناعي',
                ], 500);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'تم إضافة الطالب بنجاح',
                'data'    => new StudentResource($student->load('groups.courses')),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────
    // SHOW
    // ─────────────────────────────
    public function show(string $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data'   => new StudentResource(
                Student::with('groups.courses')->findOrFail($id)
            ),
        ]);
    }

    // ─────────────────────────────
    // UPDATE
    // ─────────────────────────────
    public function update(UpdateStudentRequest $request, string $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $data = $request->validated();

        $groupId = $data['group_id'] ?? null;
        unset($data['group_id'], $data['course_ids']);

        $newImagePath = null;
        $oldImage = $student->face_image;

        DB::beginTransaction();

        try {
            // new image
            if ($request->hasFile('face_image')) {
                $newImagePath = $request->file('face_image')->store('students/faces', 'public');
                $data['face_image'] = $newImagePath;
            }

            $student->update($data);

            if ($groupId) {
                // delete old enrollments
                $student->courseEnrollments()->delete();

                // rebuild enrollments from group
                $group = Group::with('courses')->findOrFail($groupId);

                foreach ($group->courses as $course) {
                    $student->courseEnrollments()->create([
                        'group_id'    => $groupId,
                        'course_id'   => $course->id,
                        'enrolled_at' => now(),
                    ]);
                }
            }

            // AI update face
            if ($newImagePath) {
                $aiUpdated = $this->aiService->updateFace(
                    $newImagePath,
                    $student->student_code
                );

                if (!$aiUpdated) {
                    DB::rollBack();

                    Storage::disk('public')->delete($newImagePath);

                    return response()->json([
                        'status'  => false,
                        'message' => 'فشل تحديث الوجه في نظام الذكاء الاصطناعي',
                    ], 500);
                }
            }

            // delete old image
            if ($newImagePath && $oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'تم تعديل الطالب بنجاح',
                'data'    => $student->fresh('groups.courses'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────
    // DELETE
    // ─────────────────────────────
    public function destroy(string $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        try {
            $deleted = $this->aiService->deleteFace($student->student_code);

            if (!$deleted) {
                return response()->json([
                    'status'  => false,
                    'message' => 'فشل حذف الوجه من نظام الذكاء الاصطناعي',
                ], 500);
            }

            if ($student->face_image && Storage::disk('public')->exists($student->face_image)) {
                Storage::disk('public')->delete($student->face_image);
            }

            $student->delete();

            return response()->json([
                'status'  => true,
                'message' => 'تم حذف الطالب بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
