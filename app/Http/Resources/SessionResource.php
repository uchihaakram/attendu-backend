<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'session_type' => $this->session_type,
            'session_date' => $this->session_date?->format('Y-m-d'),
            'start_time' => $this->start_time ? \Carbon\Carbon::parse($this->start_time)->format('H:i') : null,
            'end_time'   => $this->end_time   ? \Carbon\Carbon::parse($this->end_time)->format('H:i')   : null,
            'location'     => $this->location,
            'day'          => $this->day,
            'status'       => $this->status,
            'group_name' => $this->group?->group_name, // ← group_name مش name
            'course_name'  => $this->course?->course_name,
            'instructors'  => $this->instructors->pluck('name'),
        ];
    }
}
