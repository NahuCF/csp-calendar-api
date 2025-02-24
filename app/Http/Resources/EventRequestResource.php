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
            'discount_amount' => $this->discount_amount,
            'price_with_taxes' => $this->price_with_taxes,
            'total_to_pay' => $this->total_to_pay,
            'note' => $this->note,
            'tax_amount' => $this->tax_amount,
            'confirmed' => $this->confirmed,
            'client' => ClientResource::make($this->whenLoaded('client')),
            'details' => EventRequestDetailResource::collection($this->whenLoaded('details')),
            'sport' => SportResource::make($this->whenLoaded('sport')),
            'facility' => FacilityResource::make($this->whenLoaded('facility')),
            'facility_id' => $this->facility_id,
            'is_paid' => $this->is_paid,
            'rejected' => $this->rejected,
        ];
    }
}
