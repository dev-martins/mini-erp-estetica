<?php

namespace App\Domain\Clients\Repositories;

use App\Domain\Clients\DTOs\ClientData;
use App\Domain\Clients\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ClientRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Client::query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($filters['no_return_since'] ?? null, function (Builder $query, string $date): void {
                $query->where(function (Builder $builder) use ($date): void {
                    $builder->whereNull('last_appointment_at')
                        ->orWhere('last_appointment_at', '<', $date);
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(ClientData $data): Client
    {
        return Client::create($data->toArray());
    }

    public function update(Client $client, ClientData $data): Client
    {
        $client->update($data->toArray());

        return $client;
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }
}
