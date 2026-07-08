<?php
// app/Http/Controllers/API/Auth/StudentAuthController.php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StudentRegisterRequest;
use App\Http\Requests\Auth\StudentUpdateProfileRequest;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentAuthController extends Controller
{
    public function __construct(private AIService $aiService) {}

    // ─────────────────────────────
    // SIGNUP
    // ─────────────────────────────
    public function register(StudentRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $imagePath = null;

        DB::beginTransaction();

        try {
            if ($request->hasFile('face_image')) {
                $imagePath = $request->file('face_image')->store('students/faces', 'public');
            }

            // إنشاء الطالب
            $student = Student::create([
                'first_name'   => $data['first_name'],
                'last_name'    => $data['last_name'],
                'student_code' => $data['student_code'],
                'email'        => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'gender'       => $data['gender'],
                'national_id'  => $data['national_id'],
                'face_image'   => $imagePath,
                'registered_at' => now(),
            ]);

            // ربط الطالب بالجروب + المواد بتاعتها (نفس منطق StudentController)
            $group = Group::with('courses')->findOrFail($data['group_id']);

            if ($group->courses->isEmpty()) {
                $student->courseEnrollments()->create([
                    'group_id'    => $group->id,
                    'course_id'   => null,
                    'enrolled_at' => now(),
                ]);
            } else {
                foreach ($group->courses as $course) {
                    $student->courseEnrollments()->create([
                        'group_id'    => $group->id,
                        'course_id'   => $course->id,
                        'enrolled_at' => now(),
                    ]);
                }
            }

            // تسجيل الوجه في نظام الـ AI
            $enrolled = $this->aiService->enrollFace($student->face_image, $student->student_code);

            if (!$enrolled) {
                DB::rollBack();
                if ($imagePath) Storage::disk('public')->delete($imagePath);

                return response()->json([
                    'status'  => false,
                    'message' => 'فشل تسجيل الوجه في نظام الذكاء الاصطناعي',
                ], 500);
            }

            // إنشاء حساب User مرتبط بالطالب
            $user = User::create([
                'name'       => $data['first_name'] . ' ' . $data['last_name'],
                'email'      => $data['email'],
                'phone'      => $data['phone_number'] ?? null,
                'gender'     => $data['gender'],
                'role'       => 'student',
                'student_id' => $student->id,
                'password'   => Hash::make($data['password']),
            ]);

            DB::commit();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status'  => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'user'    => $user,
                'token'   => $token,
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
    // UPDATE PROFILE
    // ─────────────────────────────
    public function updateProfile(StudentUpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::findOrFail($user->student_id);

        $data = $request->validated();
        $newImagePath = null;
        $oldImage = $student->face_image;

        DB::beginTransaction();

        try {
            if ($request->hasFile('face_image')) {
                $newImagePath = $request->file('face_image')->store('students/faces', 'public');
                $data['face_image'] = $newImagePath;
            }

            $studentData = collect($data)->except(['password'])->toArray();
            $student->update($studentData);

            // تحديث الوجه في نظام الـ AI
            if ($newImagePath) {
                $updated = $this->aiService->updateFace($newImagePath, $student->student_code);

                if (!$updated) {
                    DB::rollBack();
                    Storage::disk('public')->delete($newImagePath);

                    return response()->json([
                        'status'  => false,
                        'message' => 'فشل تحديث الوجه في نظام الذكاء الاصطناعي',
                    ], 500);
                }
            }

            if ($newImagePath && $oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            // تحديث بيانات الـ User المرتبطة
            $userUpdate = [];
            if (isset($data['email'])) $userUpdate['email'] = $data['email'];
            if (isset($data['phone_number'])) $userUpdate['phone'] = $data['phone_number'];
            if (isset($data['gender'])) $userUpdate['gender'] = $data['gender'];
            if (!empty($data['password'])) $userUpdate['password'] = Hash::make($data['password']);

            if (!empty($userUpdate)) $user->update($userUpdate);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'تم تعديل البيانات بنجاح',
                'data'    => $student->fresh(),
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
}
