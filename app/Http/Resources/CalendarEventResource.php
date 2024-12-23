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
            'user_id' => $this->user_id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'calendar_resource_id' => $this->calendar_resource_id,
            'start_date' => Carbon::parse($this->start_at)->format('Y-m-d\TH:i:s'),
            'end_date' => Carbon::parse($this->end_at)->format('Y-m-d\TH:i:s'),
            'color' => $this->color,
            'is_paid' => (bool) $this->is_paid,
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
            'created_at' => $this->created_at,
        ];
    }
}
