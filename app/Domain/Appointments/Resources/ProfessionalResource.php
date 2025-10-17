<?php

namespace App\Domain\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Appointments\Models\Professional */
class ProfessionalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'specialty' => $this->specialty,
            'commission_type' => $this->commission_type,
            'commission_value' => $this->commission_value,
            'active' => (bool) $this->active,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
        ];
    }
}
