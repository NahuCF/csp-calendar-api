<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
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
            'user_id' => $this->user_id,
            'country_id' => $this->country_id,
            'country_subdivision_id' => $this->country_subdivision_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'resource_count' => $this->whenNotNull($this->resources_count),
            'country' => CountryResource::make($this->whenLoaded('country')),
            'fallback_territory_name' => $this->fallback_territory_name,
            'fallback_subterritory_name' => $this->fallback_subterritory_name,
            'resources' => CalendarResourceResource::collection($this->whenLoaded('resources')),
            'currency_code' => $this->currency_code,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'tax_percentage' => $this->tax_percentage,
        ];
    }
}
