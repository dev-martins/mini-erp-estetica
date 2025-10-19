<?php

namespace App\Domain\Clients\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Clients\Models\Client */
class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toAtomString(),
            'phone_verified_at' => $this->phone_verified_at?->toAtomString(),
            'birthdate' => optional($this->birthdate)?->format('Y-m-d'),
            'instagram' => $this->instagram,
            'consent_marketing' => (bool) $this->consent_marketing,
            'source' => $this->source,
            'last_appointment_at' => optional($this->last_appointment_at)?->toAtomString(),
            'tags' => $this->tags,
            'verification_channels' => $this->verification_channels ?? [],
            'created_at' => $this->created_at?->toAtomString(),
            'updated_at' => $this->updated_at?->toAtomString(),
        ];
    }
}
