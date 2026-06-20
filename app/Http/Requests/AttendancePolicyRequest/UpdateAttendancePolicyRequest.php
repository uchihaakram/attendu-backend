<?php

namespace App\Http\Requests\AttendancePolicyRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendancePolicyRequest extends FormRequest
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
            'max_absences_allowed' => ['sometimes', 'integer', 'min:0'],
            'min_attend'           => ['sometimes', 'integer', 'min:0'],
            'max_attend'           => ['sometimes', 'integer', 'gt:min_attend'],
        ];
    }
    public function messages(): array
    {
        return [
            'max_absences_allowed.min' => 'عدد الغيابات لا يمكن أن يكون سالباً.',
            'min_attend.min'           => 'وقت الحضور لا يمكن أن يكون سالباً.',
            'max_attend.gt'            => 'وقت التأخير يجب أن يكون أكبر من وقت الحضور الطبيعي.',
        ];
    }
}
