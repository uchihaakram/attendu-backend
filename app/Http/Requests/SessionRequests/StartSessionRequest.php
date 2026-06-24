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

            // ❌ شيلنا students — الباك إند بيجيبهم تلقائي من DB

            'start_time' => ['required', 'date'],
            'end_time'   => ['required', 'date', 'after:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'session_schedule_id.required' => 'السيشن مطلوبة.',
            'session_schedule_id.exists'   => 'السيشن غير موجودة.',

            'start_time.required'    => 'وقت البداية مطلوب.',
            'start_time.date_format' => 'وقت البداية يجب أن يكون بصيغة YYYY-MM-DDTHH:MM:SS.',

            'end_time.required'    => 'وقت النهاية مطلوب.',
            'end_time.date_format' => 'وقت النهاية يجب أن يكون بصيغة YYYY-MM-DDTHH:MM:SS.',
            'end_time.after'       => 'وقت النهاية يجب أن يكون بعد البداية.',
        ];
    }
}
