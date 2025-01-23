<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventRequestResource extends JsonResource
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
            'request_id' => $this->request_id,
            'price' => $this->price,
            'confirmed' => $this->confirmed,
            'details' => EventRequestDetailResource::collection($this->whenLoaded('details')),
            'sport' => SportResource::make($this->whenLoaded('sport')),
            'is_paid' => $this->is_paid,
        ];
    }
}
