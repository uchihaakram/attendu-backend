<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequests\StoreStudentRequest;
use App\Http\Requests\StudentRequests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Services\AIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function __construct(private AIService $aiService) {}

    // ─────────────────────────────
    // INDEX
    // ─────────────────────────────
    public function index()
    {
        $students = Student::all();

        return response()->json([
            'status' => true,
            'message' => $students->isEmpty()
                ? 'عفوا لا يوجد بيانات للعرض'
                : null,
            'data' => StudentResource::collection($students)
        ]);
    }

    // ─────────────────────────────
    // STORE
    // ─────────────────────────────
    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();
        $imagePath = null;

        DB::beginTransaction();

        try {

            // upload image (DB field = face_image)
            if ($request->hasFile('face_image')) {

                $imagePath = $request->file('face_image')
                    ->store('students/faces', 'public');

                $data['face_image'] = $imagePath;
            }

            // create student
            $student = Student::create($data);

            // send to AI (AI expects file field internally)
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

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
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
            'data' => new StudentResource(
                Student::findOrFail($id)
            )
        ]);
    }

    // ─────────────────────────────
    // UPDATE
    // ─────────────────────────────
    public function update(UpdateStudentRequest $request, string $id)
    {
        $student = Student::findOrFail($id);

        DB::beginTransaction();

        $newImagePath = null;
        $oldImage = $student->face_image;

        try {

            $data = $request->validated();

            unset($data['student_code']);

            // upload new image
            if ($request->hasFile('face_image')) {

                $newImagePath = $request->file('face_image')
                    ->store('students/faces', 'public');

                $data['face_image'] = $newImagePath;
            }

            // AI FIRST
            if ($newImagePath) {

                $aiUpdated = $this->aiService->updateFace(
                    $newImagePath,
                    $student->student_code
                );

                if (!$aiUpdated) {

                    DB::rollBack();

                    if ($newImagePath) {
                        Storage::disk('public')->delete($newImagePath);
                    }

                    return response()->json([
                        'status' => false,
                        'message' => 'AI update failed - student not updated'
                    ], 500);
                }
            }

            // update DB
            $student->update($data);

            // delete old image only when a new image was uploaded
            if ($newImagePath && $oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Student updated successfully',
                'data' => new StudentResource($student->fresh())
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
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

            $deleted = $this->aiService->deleteFace(
                $student->student_code
            );

            if (!$deleted) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete embedding from AI'
                ], 500);
            }

            if ($student->face_image && Storage::disk('public')->exists($student->face_image)) {
                Storage::disk('public')->delete($student->face_image);
            }

            $student->delete();

            return response()->json([
                'status' => true,
                'message' => 'Student deleted successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
