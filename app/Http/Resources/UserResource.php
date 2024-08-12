<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name ?? null,
            'email' => $this->email ?? null,
            'phone_number' => $this->phone_number ?? null,
            'status' => $this->status ?? null,
            'last_login' => $this->last_login ?? null,
            'role' => $this->role,
            'created_at' => $this->created_at ?? null,
        ];
    }
}
