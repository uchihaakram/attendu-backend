<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('SessionSchedules_id')->references('id')->on('SessionSchedules')->cascadeOnDelete();
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->time('check_in_time')->nullable();
            $table->string('detection_method')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
