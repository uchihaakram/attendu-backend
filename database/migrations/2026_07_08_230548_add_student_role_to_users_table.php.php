<?php

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
                ->references('id')
                ->on('students')
                ->nullOnDelete();
        });
    }

 public function down(): void
{
    Schema::table('users', function (Blueprint $table) {

        if (Schema::hasColumn('users', 'student_id')) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        }

    });

    DB::statement("ALTER TABLE users MODIFY role ENUM('admin','instructor') DEFAULT 'instructor'");
}
};
