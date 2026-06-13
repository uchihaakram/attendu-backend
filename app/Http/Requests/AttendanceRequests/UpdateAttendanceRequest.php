<?php

namespace App\Http\Requests\AttendanceRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'         => ['required', 'in:present,late,absent'],

            // اختياري — لو الأدمن عايز يحدد وقت معين للحضور اليدوي
            // لو مش موجود → بنحتفظ بالوقت القديم (present/late) أو null (absent)
            'check_in_time'  => ['nullable', 'date_format:H:i:s'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'              => 'حالة الحضور مطلوبة.',
            'status.in'                    => 'حالة الحضور يجب أن تكون: present أو late أو absent.',
            'check_in_time.date_format'    => 'وقت الحضور يجب أن يكون بصيغة HH:MM:SS.',
        ];
    }
}
