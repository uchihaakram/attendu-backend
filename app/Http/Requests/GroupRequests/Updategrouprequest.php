<?php

namespace App\Http\Requests\GroupRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'course_id' => [
                'sometimes',
                'exists:courses,id',
            ],

            'group_name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'group_code' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('groups', 'group_code')->ignore($id),
            ],

            'academic_year' => [
                'sometimes',
                Rule::in(['first', 'second', 'third', 'fourth']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.exists'  => 'المقرر غير موجود.',
            'group_code.unique' => 'كود الفرقة مستخدم بالفعل.',
            'academic_year.in'  => 'السنة الدراسية يجب أن تكون first أو second أو third أو fourth.',
        ];
    }
}
