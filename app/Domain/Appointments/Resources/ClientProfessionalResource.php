<?php

namespace App\Domain\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Appointments\Models\Professional */
class ClientProfessionalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'specialty' => $this->specialty,
            'work_hours' => $this->work_hours,
        ];
    }
}
