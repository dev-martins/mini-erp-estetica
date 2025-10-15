<?php

namespace App\Domain\Inventory\DTOs;

use Illuminate\Http\Request;

class StockMovementData
{
    public function __construct(
        public readonly int $productId,
        public readonly ?int $batchId,
        public readonly string $type,
        public readonly float $qty,
        public readonly ?float $unitCost,
        public readonly ?string $reason,
        public readonly ?array $meta,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            productId: (int) $request->input('product_id'),
            batchId: $request->input('batch_id'),
            type: (string) $request->input('type'),
            qty: (float) $request->input('qty'),
            unitCost: $request->input('unit_cost'),
            reason: $request->input('reason'),
            meta: $request->input('meta')
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'batch_id' => $this->batchId,
            'type' => $this->type,
            'qty' => $this->qty,
            'unit_cost' => $this->unitCost,
            'reason' => $this->reason,
            'meta' => $this->meta,
        ];
    }
}
