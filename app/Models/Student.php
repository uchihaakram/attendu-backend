<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Group;
use App\Models\Attendance;
use App\Models\Warning;
use App\Models\CourseEnrollment;

class Student extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $fillable = [
    //     'first_name',
    //     'last_name',
    //     'student_code',
    //     'email',
    //     'phone_number',
    //     'gender',
    //     'national_id',
    //     'image',
    //     'face_profile_id',
    //     'academic_year',
    //     'registered_at',
    // ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'course_enrollments')
            ->withTimestamps()
            ->withPivot(['course_id', 'enrolled_at']);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function warnings(): HasMany
    {
        return $this->hasMany(Warning::class);
    }

    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }
    public function faceEmbeddings()
    {
        return $this->hasMany(FaceEmbedding::class);
    }
}
