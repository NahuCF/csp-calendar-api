<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name.' '.$this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_path' => $this->avatar_path,
            'signed_waiver' => $this->signed_waiver,
            'default_location' => $this->default_location,
            'last_location' => $this->last_location,
            'preferred_position' => $this->preferred_position,
            'remember_token' => $this->remember_token,
            'email_verified_at' => $this->email_verified_at,
            'tenant_id' => $this->tenant_id,
            'country' => new CountryResource($this->country),
            'roles' => $this->whenLoaded('roles'),
            'permissions' => $this->whenNotNull($this->permissions),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
