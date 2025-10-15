<?php

namespace App\Domain\Inventory\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Inventory\Models\StockMovement */
class StockMovementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'batch_id' => $this->batch_id,
            'type' => $this->type,
            'qty' => (float) $this->qty,
            'unit_cost' => $this->unit_cost !== null ? (float) $this->unit_cost : null,
            'reason' => $this->reason,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toAtomString(),
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'current_stock' => (float) $this->product->current_stock,
            ]),
        ];
    }
}
