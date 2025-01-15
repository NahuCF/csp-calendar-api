<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventRequestDetailResource extends JsonResource
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
            'event_request_id' => $this->event_request_id,
            'price' => $this->price,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'calendar_resource_id' => $this->calendar_resource_id,
            'resource' => CalendarResourceResource::make($this->whenLoaded('resource')),
        ];
    }
}
