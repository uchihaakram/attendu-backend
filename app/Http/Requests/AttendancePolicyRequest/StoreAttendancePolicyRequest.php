<?php

namespace App\Http\Requests\AttendancePolicyRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttendancePolicyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id'            => ['required', 'exists:courses,id', 'unique:attendance_policies,course_id'],
            'max_absences_allowed' => ['required', 'integer', 'min:0'],
            'min_attend'           => ['required', 'integer', 'min:0'],
            'max_attend'           => ['required', 'integer', 'gt:min_attend'],
        ];
    }
    public function messages(): array
    {
        return [
            'course_id.required'            => 'المقرر مطلوب.',
            'course_id.exists'              => 'المقرر غير موجود.',
            'course_id.unique'              => 'يوجد سياسة حضور لهذا المقرر بالفعل.',
            'max_absences_allowed.required' => 'عدد الغيابات المسموح به مطلوب.',
            'max_absences_allowed.min'      => 'عدد الغيابات لا يمكن أن يكون سالباً.',
            'min_attend.required'           => 'وقت الحضور الطبيعي مطلوب.',
            'min_attend.min'                => 'وقت الحضور لا يمكن أن يكون سالباً.',
            'max_attend.required'           => 'وقت التأخير مطلوب.',
            'max_attend.gt'                 => 'وقت التأخير يجب أن يكون أكبر من وقت الحضور الطبيعي.',
        ];
    }
}
