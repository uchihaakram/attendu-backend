<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Course;
use App\Models\Group;
use App\Models\AttendancePolicy;
use App\Models\Session;
use App\Models\Student;
use App\Models\CourseEnrollment;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────
        // 1. USERS
        // ─────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@attendu.com'],
            [
                'name'     => 'Admin',
                'phone'    => '01000000000',
                'gender'   => 'male',
                'role'     => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        $instructor = User::firstOrCreate(
            ['email' => 'ahmed@attendu.com'],
            [
                'name'     => 'أحمد محمد',
                'phone'    => '01011111111',
                'gender'   => 'male',
                'role'     => 'instructor',
                'password' => Hash::make('password'),
            ]
        );

        // ─────────────────────────────
        // 2. COURSES
        // ─────────────────────────────
        $math = Course::firstOrCreate(
            ['course_code' => 'MATH101'],
            [
                'course_name' => 'computer vision',
                'description' => 'مقرر الرؤية الحاسوبية',
                'start_date'  => '2024-09-01',
                'end_date'    => '2025-01-31',
            ]
        );

        $physics = Course::firstOrCreate(
            ['course_code' => 'PHYS101'],
            [
                'course_name' => 'web development',
                'description' => 'مقرر تطوير الويب',
                'start_date'  => '2024-09-01',
                'end_date'    => '2025-01-31',
            ]
        );

        // ─────────────────────────────
        // 3. GROUPS (مع academic_year)
        // ─────────────────────────────
        $group1 = Group::firstOrCreate(
            ['group_code' => 'GRP-001'],
            [
                'course_id'     => $math->id,
                'group_name'    => 'عام',
                'academic_year' => 'first',
            ]
        );

        $group2 = Group::firstOrCreate(
            ['group_code' => 'GRP-002'],
            [
                'course_id'     => $physics->id,
                'group_name'    => 'برامج SW',
                'academic_year' => 'second',
            ]
        );

        // ─────────────────────────────
        // 4. ATTENDANCE POLICIES
        // ─────────────────────────────
        AttendancePolicy::firstOrCreate(
            ['course_id' => $math->id],
            [
                'max_absences_allowed' => 5,
                'min_attend'           => 10,
                'max_attend'           => 30,
            ]
        );

        AttendancePolicy::firstOrCreate(
            ['course_id' => $physics->id],
            [
                'max_absences_allowed' => 4,
                'min_attend'           => 10,
                'max_attend'           => 30,
            ]
        );

        // ─────────────────────────────
        // 5. SESSIONS
        // ─────────────────────────────
        $session1 = Session::firstOrCreate(
            [
                'course_id'    => $math->id,
                'session_date' => '2024-06-01',
                'session_type' => 'lecture',
            ],
            [
                'group_id'   => $group1->id,
                'day'        => 'السبت',
                'start_time' => '08:00:00',
                'end_time'   => '10:00:00',
                'location'   => 'المبنى الرئيسي - القاعة 101',
                'status'     => 'scheduled',
            ]
        );

        $session2 = Session::firstOrCreate(
            [
                'course_id'    => $physics->id,
                'session_date' => '2024-06-02',
                'session_type' => 'section',
            ],
            [
                'group_id'   => $group2->id,
                'day'        => 'الأحد',
                'start_time' => '10:30:00',
                'end_time'   => '12:00:00',
                'location'   => 'المبنى العلمي - القاعة 202',
                'status'     => 'scheduled',
            ]
        );

        // ─────────────────────────────
        // 6. SESSION INSTRUCTORS
        // ─────────────────────────────
        $session1->instructors()->syncWithoutDetaching([$instructor->id]);
        $session2->instructors()->syncWithoutDetaching([$instructor->id]);

        // ─────────────────────────────
        // 7. STUDENTS
        // ─────────────────────────────
        $student1 = Student::firstOrCreate(
            ['student_code' => 'S001'],
            [
                'first_name'  => 'محمد',
                'last_name'   => 'علي',
                'email'       => 'mohamed@student.com',
                'gender'      => 'male',
                'national_id' => '12345678901234',
                'face_image'  => 'default.jpg',
            ]
        );

        $student2 = Student::firstOrCreate(
            ['student_code' => 'S002'],
            [
                'first_name'  => 'سارة',
                'last_name'   => 'أحمد',
                'email'       => 'sara@student.com',
                'gender'      => 'female',
                'national_id' => '12345678901235',
                'face_image'  => 'default.jpg',
            ]
        );

        $student3 = Student::firstOrCreate(
            ['student_code' => 'S003'],
            [
                'first_name'  => 'عمر',
                'last_name'   => 'حسن',
                'email'       => 'omar@student.com',
                'gender'      => 'male',
                'national_id' => '12345678901236',
                'face_image'  => 'default.jpg',
            ]
        );

        // ─────────────────────────────
        // 8. COURSE ENROLLMENTS
        // ─────────────────────────────
        // محمد وسارة في رياضيات - السنة الأولى
        CourseEnrollment::firstOrCreate(
            [
                'student_id' => $student1->id,
                'group_id'   => $group1->id,
                'course_id'  => $math->id,
            ],
            ['enrolled_at' => now()]
        );

        CourseEnrollment::firstOrCreate(
            [
                'student_id' => $student2->id,
                'group_id'   => $group1->id,
                'course_id'  => $math->id,
            ],
            ['enrolled_at' => now()]
        );

        // عمر في فيزياء - السنة الثانية
        CourseEnrollment::firstOrCreate(
            [
                'student_id' => $student3->id,
                'group_id'   => $group2->id,
                'course_id'  => $physics->id,
            ],
            ['enrolled_at' => now()]
        );

        $this->command->info('✅ تم إضافة البيانات التجريبية بنجاح!');
    }
}
