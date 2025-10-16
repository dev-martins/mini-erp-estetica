<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Clients\Models\Client;
use App\Domain\Sales\Models\ClientPackage;
use App\Domain\Sales\Resources\ClientPackageResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientPackageController extends Controller
{
    public function index(Client $client, Request $request)
    {
        $this->authorize('view', $client);

        $serviceId = $request->integer('service_id');

        $packages = ClientPackage::query()
            ->with(['package.service'])
            ->where('client_id', $client->id)
            ->where('remaining_sessions', '>', 0)
            ->when($serviceId, function ($query, $serviceId) {
                $query->whereHas('package', fn ($sub) => $sub->where('service_id', $serviceId));
            })
            ->orderByRaw('expiry_at IS NULL')
            ->orderBy('expiry_at')
            ->get();

        return ClientPackageResource::collection($packages);
    }
}
