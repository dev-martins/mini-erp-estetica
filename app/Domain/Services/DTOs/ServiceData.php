<?php

namespace App\Domain\Services\DTOs;

use App\Domain\Services\Models\Service;
use Illuminate\Http\Request;

class ServiceData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $category,
        public readonly int $duration,
        public readonly float $listPrice,
        public readonly ?int $kitId,
        public readonly bool $active,
        public readonly ?string $description,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: (string) $request->input('name'),
            category: $request->input('category'),
            duration: (int) $request->integer('duration_min', 60),
            listPrice: (float) $request->input('list_price', 0),
            kitId: $request->input('kit_id'),
            active: (bool) $request->boolean('active', true),
            description: $request->input('description')
        );
    }

    public static function fromModel(Service $service): self
    {
        return new self(
            name: $service->name,
            category: $service->category,
            duration: (int) $service->duration_min,
            listPrice: (float) $service->list_price,
            kitId: $service->kit_id,
            active: (bool) $service->active,
            description: $service->description
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'duration_min' => $this->duration,
            'list_price' => $this->listPrice,
            'kit_id' => $this->kitId,
            'active' => $this->active,
            'description' => $this->description,
        ];
    }
}
