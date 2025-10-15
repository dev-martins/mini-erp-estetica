<?php

namespace App\Domain\Clients\Services;

use App\Domain\Clients\DTOs\ClientData;
use App\Domain\Clients\Models\Client;
use App\Domain\Clients\Repositories\ClientRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientService
{
    public function __construct(private readonly ClientRepository $repository)
    {
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function create(ClientData $data): Client
    {
        return $this->repository->create($data);
    }

    public function update(Client $client, ClientData $data): Client
    {
        return $this->repository->update($client, $data);
    }

    public function delete(Client $client): void
    {
        $this->repository->delete($client);
    }
}
