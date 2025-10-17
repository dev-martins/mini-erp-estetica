<?php

namespace App\Domain\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Appointments\Models\Room */
class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'notes' => $this->notes,
            'active' => (bool) $this->active,
        ];
    }
}
