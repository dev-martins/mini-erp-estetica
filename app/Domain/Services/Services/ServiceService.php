<?php

namespace App\Domain\Services\Services;

use App\Domain\Services\DTOs\ServiceData;
use App\Domain\Services\Models\Service;
use App\Domain\Services\Repositories\ServiceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceService
{
    public function __construct(private readonly ServiceRepository $repository)
    {
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function create(ServiceData $data): Service
    {
        return $this->repository->create($data);
    }

    public function update(Service $service, ServiceData $data): Service
    {
        return $this->repository->update($service, $data);
    }

    public function delete(Service $service): void
    {
        $this->repository->delete($service);
    }
}
