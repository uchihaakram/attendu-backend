<?php
// database/migrations/2026_07_09_000001_add_student_role_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // enum -> string عشان نضيف قيمة جديدة بسهولة مستقبلاً
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(20) DEFAULT 'instructor'");

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('student_id')
                ->nullable()
                ->after('role')
                ->constrained('students')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('student_id');
        });

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','instructor') DEFAULT 'instructor'");
    }
};
