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
                'exists:SessionSchedules,id'
            ],

            'students' => [
                'required',
                'array',
                'min:1'
            ],

            'students.*.student_code' => [
                'required',
                'string'
            ],

            'students.*.student_name' => [
                'required',
                'string'
            ],

            'min_attend' => [
                'required',
                'integer',
                'min:0'
            ],

            'max_attend' => [
                'required',
                'integer',
                'gt:min_attend'
            ],

            'start_time' => [
                'required',
                'date'
            ],

            'end_time' => [
                'required',
                'date',
                'after:start_time'
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'session_schedule_id.required' =>
                'جدول السيشن مطلوب.',

            'session_schedule_id.exists' =>
                'جدول السيشن غير موجود.',

            'students.required' =>
                'قائمة الطلاب مطلوبة.',

            'students.array' =>
                'الطلاب يجب أن يكونوا array.',

            'students.min' =>
                'يجب إضافة طالب واحد على الأقل.',

            'students.*.student_code.required' =>
                'كود الطالب مطلوب.',

            'students.*.student_name.required' =>
                'اسم الطالب مطلوب.',

            'min_attend.required' =>
                'الحد الأدنى للحضور مطلوب.',

            'max_attend.required' =>
                'الحد الأقصى للحضور مطلوب.',

            'max_attend.gt' =>
                'الحد الأقصى يجب أن يكون أكبر من الحد الأدنى.',

            'start_time.required' =>
                'وقت البداية مطلوب.',

            'end_time.required' =>
                'وقت النهاية مطلوب.',

            'end_time.after' =>
                'وقت النهاية يجب أن يكون بعد البداية.',
        ];
    }
}
