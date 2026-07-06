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

            'start_time' => ['required', 'date'],
            'end_time'   => ['required', 'date', 'after:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'session_schedule_id.required' => 'السيشن مطلوبة.',
            'session_schedule_id.exists'   => 'السيشن غير موجودة.',

            'start_time.required' => 'وقت البداية مطلوب.',
            'start_time.date'     => 'وقت البداية غير صالح.',

            'end_time.required'   => 'وقت النهاية مطلوب.',
            'end_time.date'       => 'وقت النهاية غير صالح.',
            'end_time.after'      => 'وقت النهاية يجب أن يكون بعد البداية.',
        ];
    }
}
