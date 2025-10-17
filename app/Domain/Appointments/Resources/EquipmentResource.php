<?php

namespace App\Domain\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Appointments\Models\Equipment */
class EquipmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'serial' => $this->serial,
            'maint_cycle_days' => $this->maint_cycle_days,
            'last_maintenance_at' => $this->last_maintenance_at?->toDateString(),
        ];
    }
}
