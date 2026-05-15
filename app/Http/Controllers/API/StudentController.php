<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StudentRequests\UpdateStudentRequest;
use App\Models\Student;
use App\Http\Resources\StudentResource;
use App\Http\Requests\StudentRequests\StoreStudentRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class StudentController extends \App\Http\Controllers\Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => StudentResource::collection(Student::all())
        ]);
    }

    // ✅ Store Student + AI Enrollment
    public function store(StoreStudentRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = $request->validated();

            // تخزين الصورة
            if ($request->hasFile('face_image')) {

                $data['face_image'] = $request->file('face_image')
                    ->store('students/faces', 'public');
            }

            // إنشاء الطالب
            $student = Student::create($data);

            // إرسال الصورة للـ AI
            $enrolled = $this->enrollFaceInAI($student);

            // لو الـ AI فشل
            if (!$enrolled) {

                // حذف الصورة
                if (!empty($student->face_image)) {

                    Storage::disk('public')
                        ->delete($student->face_image);
                }

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'AI enrollment failed'
                ], 500);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Student created successfully',
                'data' => new StudentResource($student)
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            // حذف الصورة لو كانت اترفعت
            if (!empty($data['face_image'] ?? null)) {

                Storage::disk('public')
                    ->delete($data['face_image']);
            }

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        return response()->json([
            'status' => true,
            'data' => new StudentResource(Student::findOrFail($id))
        ]);
    }

    // ✅ Update Student + AI Re-enrollment
    public function update(UpdateStudentRequest $request, string $id)
{
    $student = Student::findOrFail($id);

    DB::beginTransaction();

    try {

        $data = $request->validated();

        $oldImage = $student->face_image;

        if ($request->hasFile('face_image')) {
            $data['face_image'] = $request->file('face_image')
                ->store('students/faces', 'public');
        }

        $student->update($data);

        if ($request->hasFile('face_image')) {

            $updated = $this->updateFaceInAI($student->fresh());

            if (!$updated) {

                Storage::disk('public')->delete($data['face_image']);

                DB::rollBack();

                // ✅ رجّع الصورة القديمة
                $student->face_image = $oldImage;
                $student->save();

                return response()->json([
                    'status' => false,
                    'message' => 'AI update failed'
                ], 500);
            }

            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Student updated successfully',
            'data' => new StudentResource($student->fresh())
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
    // ✅ Delete Student + Delete AI Embedding
   public function destroy(string $id)
{
    $student = Student::findOrFail($id);

    $deleted = $this->deleteFaceFromAI($student);

    if (!$deleted) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to delete embedding from AI'
        ], 500);
    }

    if (
        $student->face_image &&
        Storage::disk('public')->exists($student->face_image)
    ) {
        Storage::disk('public')->delete($student->face_image);
    }

    $student->delete();

    return response()->json([
        'status' => true,
        'message' => 'Student deleted successfully'
    ]);
}


    // ─────────────────────────────────────
    // AI Helper Functions
    // ─────────────────────────────────────

    // ✅ Store Face
    private function enrollFaceInAI(Student $student): bool
    {
        try {

            $fullPath = storage_path('app/public/' . $student->face_image);

            if (!file_exists($fullPath)) {
                return false;
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => env('AI_API_KEY')
                ])
                ->attach(
                    'file',
                    file_get_contents($fullPath),
                    basename($fullPath)
                )
                ->post(
                    env('AI_SERVICE_URL') . '/upload-image',
                    [
                        'student_code' => $student->student_code,
                    ]
                );

            return $response->successful();

        } catch (\Exception $e) {

            return false;
        }
    }

    // ✅ Update Face
   private function updateFaceInAI(Student $student): bool
{
    try {

        $fullPath = storage_path('app/public/' . $student->face_image);

        if (!file_exists($fullPath)) {
            return false;
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'X-API-KEY' => env('AI_API_KEY')
            ])
            ->attach(
                'file',
                file_get_contents($fullPath),
                basename($fullPath)
            )
            ->put(
                env('AI_SERVICE_URL')
                . '/students/'
                . $student->student_code
                . '/file'
            );

        return $response->successful();

    } catch (\Exception $e) {

        return false;
    }
}


    // ✅ Delete Face Embedding
   private function deleteFaceFromAI(Student $student): bool
{
    try {

        $response = Http::timeout(30)
            ->withHeaders([
                'X-API-KEY' => env('AI_API_KEY')
            ])
            ->delete(
                env('AI_SERVICE_URL')
                . '/students/'
                . $student->student_code
            );

        return $response->successful();

    } catch (\Exception $e) {

        return false;
    }
}
}


// ✅ Delete Student + Delete AI Embedding

