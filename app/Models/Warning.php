<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warning extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'warning_type',
        'warning_reason',
        'status',
    ];

    protected $casts = [
        'warning_type' => 'string',
        'status'       => 'string',
    ];

    // العلاقات
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // Helper: الاسم العربي لنوع التحذير
    public function getWarningTypeLabelAttribute(): string
    {
        return match($this->warning_type) {
            'first_warning'  => 'تحذير أول',
            'second_warning' => 'تحذير ثاني',
            'final_warning'  => 'تحذير نهائي',
            default          => $this->warning_type,
        };
    }

    // Helper: الاسم العربي للحالة
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active'   => 'قيد نشط',
            'resolved' => 'تم الحل',
            default    => $this->status,
        };
    }
     public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }
}
