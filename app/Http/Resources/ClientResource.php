<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'cellphone' => $this->cellphone,
            'prefix' => $this->prefix,
            'user' => UserResource::make($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'full_cellphone' => $this->prefix.' '.$this->cellphone,
            'events_count' => $this->whenNotNull($this->events_count),
        ];
    }
}
