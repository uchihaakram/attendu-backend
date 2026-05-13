<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
              'id' => $this->id,
            'student_code' => $this->student_code,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'gender' => $this->gender,
            'national_id' => $this->national_id,
            'academic_year' => $this->academic_year,
            'face_image' => $this->face_image
                ? asset('storage/' . $this->face_image)
                : null,
            ];
    }
}
