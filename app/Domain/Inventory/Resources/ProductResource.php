<?php

namespace App\Domain\Inventory\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Inventory\Models\Product */
class ProductResource extends JsonResource
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
            'unit' => $this->unit,
            'cost_per_unit' => (float) $this->cost_per_unit,
            'min_stock' => (float) $this->min_stock,
            'expiry_control' => (bool) $this->expiry_control,
            'active' => (bool) $this->active,
            'supplier' => $this->whenLoaded('supplier', fn () => [
                'id' => $this->supplier->id,
                'name' => $this->supplier->name,
            ]),
            'created_at' => $this->created_at?->toAtomString(),
            'updated_at' => $this->updated_at?->toAtomString(),
        ];
    }
}
