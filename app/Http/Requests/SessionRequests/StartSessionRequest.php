<?php

namespace App\Http\Requests\SessionRequests;

use Illuminate\Foundation\Http\FormRequest;

class StartSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_schedule_id' => [
                'required',
                'exists:sessionschedules,id',
            ],

            'students' => [
                'required',
                'array',
                'min:1',
            ],

            'students.*.student_code' => [
                'required',
                'string',
            ],

            'students.*.student_name' => [
                'required',
                'string',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'session_schedule_id.required' => 'السيشن مطلوبة.',
            'session_schedule_id.exists'   => 'السيشن غير موجودة.',

            'students.required'   => 'قائمة الطلاب مطلوبة.',
            'students.array'      => 'الطلاب يجب أن يكونوا array.',
            'students.min'        => 'يجب إضافة طالب واحد على الأقل.',

            'students.*.student_code.required' => 'كود الطالب مطلوب.',
            'students.*.student_name.required' => 'اسم الطالب مطلوب.',

            'start_time.required'    => 'وقت البداية مطلوب.',
            'start_time.date_format' => 'وقت البداية يجب أن يكون بصيغة HH:MM.',

            'end_time.required'    => 'وقت النهاية مطلوب.',
            'end_time.date_format' => 'وقت النهاية يجب أن يكون بصيغة HH:MM.',
            'end_time.after'       => 'وقت النهاية يجب أن يكون بعد البداية.',
        ];
    }
}
