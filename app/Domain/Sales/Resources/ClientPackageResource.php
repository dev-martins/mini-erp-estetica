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
                'service_id' => $this->package?->service_id,
                'sessions_count' => $this->package?->sessions_count,
                'price' => $this->package?->price,
                'min_interval_hours' => $this->package?->min_interval_hours,
                'service_min_interval_hours' => $this->package?->service?->min_interval_hours,
            ],
        ];
    }
}
