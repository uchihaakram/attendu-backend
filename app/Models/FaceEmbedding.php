<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceEmbedding extends Model
{
    protected $fillable = [
        'student_id',
        'embedding',
        'confidence',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
