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
            'id' => $this->id,
            'student_code' => $this->student_code,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'gender' => $this->gender,
            'national_id' => $this->national_id,
            'image' => $this->image,
            'face_profile_id' => $this->face_profile_id,
            'academic_year' => $this->academic_year,
            'registered_at' => $this->registered_at,
            'face_embeddings' => FaceEmbeddingResource::collection($this->whenLoaded('faceEmbeddings')),
        ];
    }
}
