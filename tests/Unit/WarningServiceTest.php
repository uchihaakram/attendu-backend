<?php

namespace Tests\Unit;

use App\Mail\WarningNotificationMail;
use App\Models\Attendance;
use App\Models\AttendancePolicy;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Group;
use App\Models\Session;
use App\Models\Student;
use App\Models\Warning;
use App\Services\WarningService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WarningServiceTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('groups', function ($table) {
            $table->id();
            $table->string('group_name');
            $table->string('academic_year')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function ($table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('student_code')->unique();
            $table->string('email');
            $table->timestamps();
        });

        Schema::create('courses', function ($table) {
            $table->id();
            $table->string('course_name');
            $table->timestamps();
        });

        Schema::create('sessionschedules', function ($table) {
            $table->id();
            $table->foreignId('course_id');
            $table->foreignId('group_id');
            $table->date('session_date');
            $table->timestamps();
        });

        Schema::create('course_enrollments', function ($table) {
            $table->id();
            $table->foreignId('student_id');
            $table->foreignId('group_id');
            $table->foreignId('course_id');
            $table->timestamps();
        });

        Schema::create('attendance_policies', function ($table) {
            $table->id();
            $table->foreignId('course_id');
            $table->integer('max_absences_allowed');
            $table->timestamps();
        });

        Schema::create('attendances', function ($table) {
            $table->id();
            $table->foreignId('student_id');
            $table->foreignId('session_schedule_id');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('warnings', function ($table) {
            $table->id();
            $table->foreignId('student_id');
            $table->foreignId('course_id');
            $table->string('warning_type');
            $table->string('warning_reason')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_creates_warning_and_tries_to_send_email_without_failing_the_service(): void
    {
        $student = Student::create([
            'first_name' => 'Ahmed',
            'last_name' => 'Ali',
            'student_code' => 'STU001',
            'email' => 'student@example.com',
        ]);

        $course = Course::create([
            'course_name' => 'Math',
        ]);

        $group = Group::create([
            'group_name' => 'G1',
            'academic_year' => 'first',
        ]);

        $session = Session::create([
            'course_id' => $course->id,
            'group_id' => $group->id,
            'session_date' => now()->toDateString(),
        ]);

        CourseEnrollment::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'course_id' => $course->id,
        ]);

        AttendancePolicy::create([
            'course_id' => $course->id,
            'max_absences_allowed' => 1,
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'session_schedule_id' => $session->id,
            'status' => 'absent',
        ]);
        Attendance::create([
            'student_id' => $student->id,
            'session_schedule_id' => $session->id,
            'status' => 'absent',
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->with('student@example.com')
            ->andReturnSelf();

        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('mail failed'));

        $service = new WarningService();

        $service->checkAndWarn($student->id, $session->id);

        $warning = Warning::query()->first();

        $this->assertNotNull($warning);
        $this->assertSame('first_warning', $warning->warning_type);
        $this->assertNull($warning->email_sent_at);
    }
}
