<?php

namespace App\Domain\Sales\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Sales\Models\Package */
class PackageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sessions_count' => $this->sessions_count,
            'min_interval_hours' => $this->min_interval_hours,
            'price' => (float) $this->price,
            'expiry_days' => $this->expiry_days,
            'active' => (bool) $this->active,
            'service' => $this->when(
                $this->relationLoaded('service') && $this->service,
                fn () => [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                    'category' => $this->service->category,
                    'duration_min' => $this->service->duration_min,
                    'min_interval_hours' => $this->service->min_interval_hours,
                ]
            ),
        ];
    }
}
