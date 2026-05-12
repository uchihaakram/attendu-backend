<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'max_absences_allowed',
        'min_attendance_percent',
        'late_limit',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
