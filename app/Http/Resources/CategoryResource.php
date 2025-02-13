<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'color' => $this->color,
            'user' => UserResource::make($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'events_count' => $this->id == 1 ? 1 : $this->whenNotNull($this->events_count),
        ];
    }
}
