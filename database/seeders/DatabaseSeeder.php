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
                'course_name' => 'الرياضيات',
                'description' => 'مقرر الرياضيات للفرقة الأولى',
                'start_date'  => '2024-09-01',
                'end_date'    => '2025-01-31',
            ]
        );

        $physics = Course::firstOrCreate(
            ['course_code' => 'PHYS101'],
            [
                'course_name' => 'الفيزياء',
                'description' => 'مقرر الفيزياء للفرقة الثانية',
                'start_date'  => '2024-09-01',
                'end_date'    => '2025-01-31',
            ]
        );

        // ─────────────────────────────
        // 3. GROUPS
        // ─────────────────────────────
        $group1 = Group::firstOrCreate(
            ['group_code' => 'GRP-001'],
            [
                'course_id'  => $math->id,
                'group_name' => 'الفرقة الأولى',
            ]
        );

        $group2 = Group::firstOrCreate(
            ['group_code' => 'GRP-002'],
            [
                'course_id'  => $physics->id,
                'group_name' => 'الفرقة الثانية',
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
        // syncWithoutDetaching عشان ميكررش لو موجود
        $session1->instructors()->syncWithoutDetaching([$instructor->id]);
        $session2->instructors()->syncWithoutDetaching([$instructor->id]);

        // ─────────────────────────────
        // 7. STUDENTS
        // ─────────────────────────────
        Student::firstOrCreate(
            ['student_code' => 'S001'],
            [
                'first_name'    => 'محمد',
                'last_name'     => 'علي',
                'email'         => 'mohamed@student.com',
                'gender'        => 'male',
                'national_id'   => '12345678901234',
                'face_image'    => 'default.jpg',
                'academic_year' => 'first',
            ]
        );

        Student::firstOrCreate(
            ['student_code' => 'S002'],
            [
                'first_name'    => 'سارة',
                'last_name'     => 'أحمد',
                'email'         => 'sara@student.com',
                'gender'        => 'female',
                'national_id'   => '12345678901235',
                'face_image'    => 'default.jpg',
                'academic_year' => 'first',
            ]
        );

        Student::firstOrCreate(
            ['student_code' => 'S003'],
            [
                'first_name'    => 'عمر',
                'last_name'     => 'حسن',
                'email'         => 'omar@student.com',
                'gender'        => 'male',
                'national_id'   => '12345678901236',
                'face_image'    => 'default.jpg',
                'academic_year' => 'second',
            ]
        );

        $this->command->info('✅ تم إضافة البيانات التجريبية بنجاح!');
    }
}
