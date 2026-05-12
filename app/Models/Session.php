<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Course;
use App\Models\Group;
use App\Models\Attendance;
use App\Models\SessionInstructor;

class Session extends Model
{
    use HasFactory;

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
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
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
        return $this->hasMany(Attendance::class);
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'session_instructors')
            ->withTimestamps();
    }

    public function sessionInstructors(): HasMany
    {
        return $this->hasMany(SessionInstructor::class);
    }
}
