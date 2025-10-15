<?php

namespace App\Domain\Inventory\DTOs;

use Illuminate\Http\Request;

class ProductData
{
    public function __construct(
        public readonly string $name,
        public readonly string $unit,
        public readonly float $costPerUnit,
        public readonly float $minStock,
        public readonly bool $expiryControl,
        public readonly float $currentStock,
        public readonly ?int $supplierId,
        public readonly bool $active,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->input('name'),
            unit: (string) $request->input('unit'),
            costPerUnit: (float) $request->input('cost_per_unit', 0),
            minStock: (float) $request->input('min_stock', 0),
            expiryControl: (bool) $request->boolean('expiry_control'),
            currentStock: (float) $request->input('current_stock', 0),
            supplierId: $request->input('supplier_id'),
            active: (bool) $request->boolean('active', true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'unit' => $this->unit,
            'cost_per_unit' => $this->costPerUnit,
            'min_stock' => $this->minStock,
            'current_stock' => $this->currentStock,
            'expiry_control' => $this->expiryControl,
            'supplier_id' => $this->supplierId,
            'active' => $this->active,
        ];
    }
}
