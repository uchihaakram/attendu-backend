<?php

namespace App\Http\Requests\AttendanceRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_schedule_id' => ['required', 'exists:sessionschedules,id'],

            'attendance_data'                  => ['required', 'array'],
            'attendance_data.summary'          => ['required', 'array'],
            'attendance_data.present_students' => ['present', 'array'],  // ← present مش required
            'attendance_data.late_students'    => ['present', 'array'],
            'attendance_data.absent_students'  => ['present', 'array'],

            // present
            'attendance_data.present_students.*.student_code'     => ['required', 'string'],
            'attendance_data.present_students.*.confidence_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'attendance_data.present_students.*.check_in_time'    => ['nullable', 'date'],

            // late
            'attendance_data.late_students.*.student_code'        => ['required', 'string'],
            'attendance_data.late_students.*.confidence_score'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'attendance_data.late_students.*.check_in_time'       => ['nullable', 'date'],

            // absent
            'attendance_data.absent_students.*.student_code'      => ['required', 'string'],
            'attendance_data.absent_students.*.confidence_score'  => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'session_schedule_id.required' => 'معرف الجلسة مطلوب.',
            'session_schedule_id.exists'   => 'الجلسة غير موجودة.',

            'attendance_data.required' => 'بيانات الحضور مطلوبة.',
            'attendance_data.array'    => 'بيانات الحضور يجب أن تكون مصفوفة.',

            'attendance_data.present_students.present' => 'قائمة الطلاب الحاضرين مطلوبة.',
            'attendance_data.late_students.present'    => 'قائمة الطلاب المتأخرين مطلوبة.',
            'attendance_data.absent_students.present'  => 'قائمة الطلاب الغائبين مطلوبة.',
            'attendance_data.summary.required'         => 'ملخص الحضور مطلوب.',

            'attendance_data.present_students.*.student_code.required' => 'كود الطالب الحاضر مطلوب.',
            'attendance_data.late_students.*.student_code.required'    => 'كود الطالب المتأخر مطلوب.',
            'attendance_data.absent_students.*.student_code.required'  => 'كود الطالب الغائب مطلوب.',

            'attendance_data.present_students.*.confidence_score.numeric' => 'درجة الثقة يجب أن تكون رقمًا.',
            'attendance_data.late_students.*.confidence_score.numeric'    => 'درجة الثقة يجب أن تكون رقمًا.',
            'attendance_data.absent_students.*.confidence_score.numeric'  => 'درجة الثقة يجب أن تكون رقمًا.',

            'attendance_data.present_students.*.confidence_score.min' => 'درجة الثقة يجب أن تكون بين 0 و 100.',
            'attendance_data.present_students.*.confidence_score.max' => 'درجة الثقة يجب أن تكون بين 0 و 100.',

            'attendance_data.present_students.*.check_in_time.date' => 'وقت الحضور يجب أن يكون تاريخاً صحيحاً.',
            'attendance_data.late_students.*.check_in_time.date'    => 'وقت الحضور يجب أن يكون تاريخاً صحيحاً.',
        ];
    }
}
