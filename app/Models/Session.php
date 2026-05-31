<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Course;
use App\Models\Group;
use App\Models\Attendance;

class Session extends Model
{
    use HasFactory;

    protected $table = 'sessionschedules'; // ← ضيف السطر ده

    protected $fillable = [
        'course_id',
        'group_id',
        'session_type',
        'session_date',
        'day',
        'start_time',
        'end_time',
        'location',
        'status',
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'session_schedule_id');
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'session_instructors',
            'session_schedule_id',  // foreign key في pivot table
            'user_id'
        )->withTimestamps();
    }

    public function attendancePolicy(): HasOne
    {
        return $this->hasOne(AttendancePolicy::class, 'course_id', 'course_id');
    }
}
