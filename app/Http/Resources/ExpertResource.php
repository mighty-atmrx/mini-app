<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'biography' => $this->biography,
            'photo' => asset($this->photo),
            'experience' => $this->experience,
            'education' => $this->education,
            'categories' => $this->categories->pluck('category'),
        ];
    }
}
