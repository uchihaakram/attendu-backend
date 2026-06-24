<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // حذف الأعمدة القديمة مباشرة (مفيش foreign key عليهم)
        Schema::table('warnings', function (Blueprint $table) {
            $table->dropColumn(['SessionSchedules_id', 'warning_type', 'warning_reason']);
        });

        // إضافة الأعمدة الجديدة
        Schema::table('warnings', function (Blueprint $table) {
            $table->foreignId('course_id')
                ->after('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('warning_type', [
                'first_warning',
                'second_warning',
                'final_warning',
            ])->after('course_id')->default('first_warning');

            $table->string('warning_reason')->nullable()->after('warning_type');

            $table->enum('status', ['active', 'resolved'])
                ->default('active')
                ->after('warning_reason');
        });
    }

    public function down(): void
    {
        Schema::table('warnings', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn(['course_id', 'warning_type', 'warning_reason', 'status']);

            $table->unsignedBigInteger('SessionSchedules_id')->nullable();
            $table->enum('warning_type', ['attendance', 'behavior', 'manual'])->default('attendance');
            $table->string('warning_reason')->nullable();
        });
    }
};
