<?php

namespace App\Http\Requests\GroupRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
        'course_ids'   => ['required', 'array'],
        'course_ids.*' => ['exists:courses,id'],

        'group_name'    => ['required', 'string', 'max:255'],
        'group_code'    => ['required', 'string', 'max:50', 'unique:groups,group_code'],
        'academic_year' => ['required', 'in:first,second,third,fourth'],
    ];
    }

    public function messages(): array
    {
        return [
            'course_ids.required'     => 'المقررات مطلوبة.',
            'course_ids.array'        => 'المقررات يجب أن تكون مصفوفة.',
            'course_ids.*.exists'     => 'بعض المقررات غير موجودة.',
            'group_name.required'    => 'اسم الفرقة مطلوب.',
            'group_code.required'    => 'كود الفرقة مطلوب.',
            'group_code.unique'      => 'كود الفرقة مستخدم بالفعل.',
            'academic_year.required' => 'السنة الدراسية مطلوبة.',
            'academic_year.in'       => 'السنة الدراسية يجب أن تكون first أو second أو third أو fourth.',
        ];
    }
}
