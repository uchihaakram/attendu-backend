<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequests\StoreStudentRequest;
use App\Http\Requests\StudentRequests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\Group;
use App\Services\AIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function __construct(private AIService $aiService) {}

    // ─────────────────────────────
    // INDEX
    // ─────────────────────────────
    public function index()
    {
        $students = Student::with('groups.course')->paginate(10);

        return response()->json([
            'status'  => true,
            'message' => $students->isEmpty() ? 'عفوا لا يوجد بيانات للعرض' : null,
            'data'    => StudentResource::collection($students),
        ]);
    }

    // ─────────────────────────────
    // STORE
    // ─────────────────────────────
    public function store(StoreStudentRequest $request)
    {
        $data      = $request->validated();
        $groupId   = $data['group_id'];
        $courseIds = $data['course_ids'];
        unset($data['group_id'], $data['course_ids']);
        $imagePath = null;

        DB::beginTransaction();

        try {
            // رفع الصورة
            if ($request->hasFile('face_image')) {
                $imagePath          = $request->file('face_image')->store('students/faces', 'public');
                $data['face_image'] = $imagePath;
            }

            // إنشاء الطالب
            $student = Student::create($data);

            // ربط الطالب بالجروب وكل الكورسات
            foreach ($courseIds as $courseId) {
                $student->courseEnrollments()->create([
                    'group_id'    => $groupId,
                    'course_id'   => $courseId,
                    'enrolled_at' => now(),
                ]);
            }

            // بعت الصورة للـ AI
            $enrolled = $this->aiService->enrollFace(
                $student->face_image,
                $student->student_code
            );

            if (!$enrolled) {
                DB::rollBack();
                if ($imagePath) Storage::disk('public')->delete($imagePath);

                return response()->json([
                    'status'  => false,
                    'message' => 'فشل تسجيل الوجه في نظام الذكاء الاصطناعي',
                ], 500);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'تم إضافة الطالب بنجاح',
                'data'    => new StudentResource($student->load('groups.course')),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($imagePath) Storage::disk('public')->delete($imagePath);

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────
    // SHOW
    // ─────────────────────────────
    public function show(string $id)
    {
        return response()->json([
            'status' => true,
            'data'   => new StudentResource(
                Student::with('groups.course')->findOrFail($id)
            ),
        ]);
    }

    // ─────────────────────────────
    // UPDATE
    // ─────────────────────────────
    public function update(UpdateStudentRequest $request, string $id)
    {
        $student      = Student::findOrFail($id);
        $newImagePath = null;
        $oldImage     = $student->face_image;

        DB::beginTransaction();

        try {
            $data = $request->validated();
            unset($data['student_code']);

            // لو في تغيير في الجروب أو الكورسات
            $groupId   = $data['group_id']   ?? null;
            $courseIds = $data['course_ids'] ?? null;
            unset($data['group_id'], $data['course_ids']);

            if ($request->hasFile('face_image')) {
                $newImagePath       = $request->file('face_image')->store('students/faces', 'public');
                $data['face_image'] = $newImagePath;
            }

            if ($newImagePath) {
                $aiUpdated = $this->aiService->updateFace(
                    $newImagePath,
                    $student->student_code
                );

                if (!$aiUpdated) {
                    DB::rollBack();
                    if ($newImagePath) Storage::disk('public')->delete($newImagePath);

                    return response()->json([
                        'status'  => false,
                        'message' => 'فشل تحديث الوجه في نظام الذكاء الاصطناعي',
                    ], 500);
                }
            }

            // تحديث بيانات الطالب
            $student->update($data);

            // لو في تغيير في الكورسات أو الجروب
            if ($courseIds !== null && $groupId !== null) {
                // مسح التسجيلات القديمة وإضافة الجديدة
                $student->courseEnrollments()->delete();

                foreach ($courseIds as $courseId) {
                    $student->courseEnrollments()->create([
                        'group_id'    => $groupId,
                        'course_id'   => $courseId,
                        'enrolled_at' => now(),
                    ]);
                }
            }

            if ($newImagePath && $oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'تم تعديل الطالب بنجاح',
                'data'    => new StudentResource($student->fresh('groups.course')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($newImagePath) Storage::disk('public')->delete($newImagePath);

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────
    // DELETE
    // ─────────────────────────────
    public function destroy(string $id)
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
