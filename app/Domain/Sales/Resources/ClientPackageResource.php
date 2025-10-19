<?php

namespace App\Domain\Sales\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Sales\Models\ClientPackage */
class ClientPackageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'remaining_sessions' => $this->remaining_sessions,
            'purchased_at' => $this->purchased_at?->toAtomString(),
            'expiry_at' => $this->expiry_at?->toAtomString(),
            'package' => [
                'id' => $this->package?->id,
                'name' => $this->package?->name,
                'description' => $this->package?->description,
                'sessions_count' => $this->package?->sessions_count,
                'price' => (float) $this->package?->price,
                'min_interval_hours' => $this->package?->min_interval_hours,
                'expiry_days' => $this->package?->expiry_days,
                'service' => $this->package?->service ? [
                    'id' => $this->package->service->id,
                    'name' => $this->package->service->name,
                    'category' => $this->package->service->category,
                    'duration_min' => $this->package->service->duration_min,
                    'min_interval_hours' => $this->package->service->min_interval_hours,
                ] : null,
            ],
        ];
    }
}
