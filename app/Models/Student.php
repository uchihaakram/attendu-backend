<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $guarded = [];


    protected $casts = [
        'registered_at' => 'datetime',
    ];

    // Groups (pivot student_group)
    // Groups (via course_enrollments)
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'course_enrollments')
            ->withTimestamps()
            ->distinct();
    }

    // Course Enrollments (IMPORTANT FIX)
    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function warnings(): HasMany
    {
        return $this->hasMany(Warning::class);
    }

    public function faceEmbeddings(): HasMany
    {
        return $this->hasMany(FaceEmbedding::class);
    }
}
