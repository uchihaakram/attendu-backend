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
            'course_ids'   => ['sometimes', 'array'],
            'course_ids.*' => ['exists:courses,id'],

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
            'group_code.unique' => 'كود الفرقة مستخدم بالفعل.',
            'academic_year.in'  => 'السنة الدراسية يجب أن تكون first أو second أو third أو fourth.',
            "course_ids.*.exists" => "بعض المقررات غير موجودة.",
            "group_name.string" => "اسم الفرقة يجب أن يكون نصًا.",
            "group_name.max" => "اسم الفرقة لا يجب أن يتجاوز 255 حرفًا.",
            "group_code.string" => "كود الفرقة يجب أن يكون نصًا.",
            "group_code.max" => "كود الفرقة لا يجب أن يتجاوز 50 حرفًا.",
        ];
    }
}
