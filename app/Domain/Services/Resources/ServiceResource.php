<?php

namespace App\Domain\Services\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Services\Models\Service */
class ServiceResource extends JsonResource
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
            'name' => $this->name,
            'category' => $this->category,
            'duration_min' => $this->duration_min,
            'min_interval_hours' => $this->min_interval_hours,
            'list_price' => (float) $this->list_price,
            'kit_id' => $this->kit_id,
            'active' => (bool) $this->active,
            'description' => $this->description,
            'created_at' => $this->created_at?->toAtomString(),
            'updated_at' => $this->updated_at?->toAtomString(),
        ];
    }
}
