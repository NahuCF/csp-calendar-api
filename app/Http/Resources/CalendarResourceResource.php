<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarResourceResource extends JsonResource
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
            'tenant_id' => $this->tenant_id,
            'user_id' => $this->user_id,
            'facility_id' => $this->facility_id,
            'calendar_resource_type_id' => $this->calendar_resource_type_id,
            'facility' => FacilityResource::make($this->whenLoaded('facility')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'resource_type' => CalendarResourceTypeResource::make($this->whenLoaded('calendarResourceType')),
            'events_count' => $this->whenNotNull($this->events_count),
            'price' => $this->price,
            'facilities' => FacilityResource::collection($this->whenLoaded('facilities')),
        ];
    }
}
