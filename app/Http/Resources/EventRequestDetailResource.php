<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

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
            'start_at_date' => Carbon::parse($this->start_at)->format('m/d/Y'),
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'end_time' => Carbon::parse($this->end_at)->format('H:i'),
            'start_time' => Carbon::parse($this->start_at)->format('H:i'),
            'calendar_resource_id' => $this->calendar_resource_id,
            'resource' => CalendarResourceResource::make($this->whenLoaded('resource')),
        ];
    }
}
