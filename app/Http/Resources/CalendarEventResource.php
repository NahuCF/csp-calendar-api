<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class CalendarEventResource extends JsonResource
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
            'client_id' => $this->client_id,
            'category_id' => $this->category_id,
            'user_id' => $this->user_id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'calendar_resource_id' => $this->calendar_resource_id,
            'start_date' => Carbon::parse($this->start_at)->format('Y-m-d\TH:i:s'),
            'end_date' => Carbon::parse($this->end_at)->format('Y-m-d\TH:i:s'),
            'is_paid' => (bool) $this->is_paid,
            'type' => $this->type,
            'cancelation_reason' => $this->cancelation_reason,
            'resource' => CalendarResourceResource::make($this->whenLoaded('resource')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'price' => $this->price,
            'real_price' => $this->discount || $this->discount_percentage
                ?
                    ($this->discount
                        ? number_format($this->price - $this->discount, 2)
                        : number_format($this->price * (1 - $this->discount_percentage / 100), 2))
                : $this->price,
            'discount_type' => $this->discount || $this->discount_percentage
                ?
                    ($this->discount
                        ? 'fixed'
                        : 'percentage')
                : null,
            'discount' => $this->discount,
            'discount_percentage' => $this->discount_percentage,
            'notes' => EventNoteResource::collection($this->whenLoaded('notes')),
            'color' => $this->category->color,
            'will_assist' => $this->will_assist,
            'client' => ClientResource::make($this->whenLoaded('client')),
            'creation_date' => Carbon::parse($this->created_at)->format('d/m/Y h:i A'),
            'sport_id' => $this->sport_id,
            'sport' => SportResource::make($this->whenLoaded('sport')),
            'reservations' => $this->whenNotNull($this->reservations),
            'created_at' => $this->created_at,
        ];
    }
}
