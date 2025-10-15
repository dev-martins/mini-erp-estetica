<?php

namespace App\Domain\Services\Repositories;

use App\Domain\Services\DTOs\ServiceData;
use App\Domain\Services\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ServiceRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Service::query()
            ->when($filters['active'] ?? null, fn (Builder $query) => $query->where('active', true))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(ServiceData $data): Service
    {
        return Service::create($data->toArray());
    }

    public function update(Service $service, ServiceData $data): Service
    {
        $service->update($data->toArray());

        return $service;
    }

    public function delete(Service $service): void
    {
        $service->delete();
    }
}
