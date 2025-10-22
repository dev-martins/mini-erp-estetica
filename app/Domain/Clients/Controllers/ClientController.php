<?php

namespace App\Domain\Clients\Controllers;

use App\Domain\Clients\DTOs\ClientData;
use App\Domain\Clients\Models\Client;
use App\Domain\Clients\Requests\ClientRequest;
use App\Domain\Clients\Resources\ClientResource;
use App\Domain\Clients\Services\ClientService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $service)
    {
        $this->authorizeResource(Client::class, 'client');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->service->list(
            filters: $request->only(['search', 'no_return_since', 'status']),
            perPage: $request->integer('per_page', 15)
        );

        return ClientResource::collection($clients);
    }

    public function store(ClientRequest $request)
    {
        $client = $this->service->create(ClientData::fromRequest($request));

        return ClientResource::make($client)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Client $client)
    {
        return ClientResource::make($client);
    }

    public function update(ClientRequest $request, Client $client)
    {
        $updated = $this->service->update($client, ClientData::fromRequest($request));

        return ClientResource::make($updated);
    }

    public function updateStatus(Request $request, int $client)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $model = $this->service->findWithTrashed($client);

        $this->authorize('update', $model);

        $updated = $this->service->changeStatus($model, $data['status']);

        return ClientResource::make($updated);
    }

    public function destroy(Client $client)
    {
        $this->service->delete($client);

        return response()->noContent();
    }
}
