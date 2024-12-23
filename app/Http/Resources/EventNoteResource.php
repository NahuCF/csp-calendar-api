<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class EventNoteResource extends JsonResource
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
            'note' => $this->note,
            'user_id' => $this->user_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'calendar_event_id' => $this->calendar_event_id,
            'creation_date' => Carbon::parse($this->created_at)->format('d/m/Y h:i A'),
        ];
    }
}
