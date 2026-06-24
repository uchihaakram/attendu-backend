<?php

namespace App\Http\Requests\SessionRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id'    => ['required', 'exists:courses,id'],
            'group_id'     => ['required', 'exists:groups,id'],
            'session_type' => ['required', 'in:lecture,section,lab'],
            'session_date' => ['required', 'date'],
            'day'          => ['required', 'string'],

            'start_time' => ['required', 'date'],
            'end_time'   => ['required', 'date', 'after:start_time'],
            'location'     => ['nullable', 'string'],
            'instructor_ids'   => ['required', 'array', 'min:1'],
            'instructor_ids.*' => ['exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required'       => 'المقرر مطلوب.',
            'course_id.exists'         => 'المقرر غير موجود.',
            'group_id.required'        => 'الفرقة الدراسية مطلوبة.',
            'group_id.exists'          => 'الفرقة الدراسية غير موجودة.',
            'session_type.required'    => 'نوع الجلسة مطلوب.',
            'session_type.in'          => 'نوع الجلسة يجب أن يكون lecture أو section أو lab.',
            'session_date.required'    => 'تاريخ الجلسة مطلوب.',
            'session_date.date'        => 'تاريخ الجلسة غير صحيح.',
            'day.required'             => 'اليوم مطلوب.',
            'start_time.required'    => 'وقت البداية مطلوب.',
            'start_time.date_format' => 'وقت البداية يجب أن يكون بصيغة YYYY-MM-DDTHH:MM:SS.',
            'end_time.required'    => 'وقت النهاية مطلوب.',
            'end_time.date_format' => 'وقت النهاية يجب أن يكون بصيغة YYYY-MM-DDTHH:MM:SS.',
            'end_time.after'       => 'وقت النهاية يجب أن يكون بعد البداية.',
            'instructor_ids.required'  => 'المحاضر مطلوب.',
            'instructor_ids.*.exists'  => 'أحد المحاضرين غير موجود.',
        ];
    }
}
