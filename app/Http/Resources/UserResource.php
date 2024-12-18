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
            'remember_token' => $this->remember_token,
            'email_verified_at' => $this->email_verified_at,
            'tenant_id' => $this->tenant_id,
            'country' => new CountryResource($this->country),
            'roles' => $this->whenLoaded('roles'),
            'permissions' => $this->whenNotNull($this->permissions),
            'password_plain_text' => $this->whenNotNull($this->password_plain_text),
            'api_token' => $this->whenNotNull($this->api_token),
            'avatar_url' => $this->avatar ? env('APP_URL').'/storage/avatars/'.$this->avatar : '',
        ];
    }
}
