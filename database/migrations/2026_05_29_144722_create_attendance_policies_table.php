<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_policies', function (Blueprint $table) {

            $table->id();

            $table->foreignId('course_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // عدد الغيابات المسموح بها
            $table->unsignedInteger('max_absences_allowed')
                  ->default(0);

            // آخر وقت للحضور الطبيعي بالدقائق
            $table->unsignedInteger('min_attend')
                  ->default(10);

            // آخر وقت للتأخير بالدقائق
            $table->unsignedInteger('max_attend')
                  ->default(30);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_policies');
    }
};
