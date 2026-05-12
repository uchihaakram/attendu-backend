<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('SessionSchedules_id')->referencedTable('SessionSchedules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['SessionSchedules_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_instructors');
    }
};
