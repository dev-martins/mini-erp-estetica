<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Clients\Models\Client;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Resources\SaleResource;
use App\Domain\Sales\Services\SaleService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientSaleController extends Controller
{
    public function __construct(private readonly SaleService $sales)
    {
    }

    public function index(Request $request)
    {
        /** @var Client $client */
        $client = $request->user('client');

        $filters = [
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'client_id' => $client->id,
        ];

        $sales = $this->sales->list(
            $filters,
            $request->integer('per_page', 15)
        );

        return SaleResource::collection($sales);
    }

    public function show(Request $request, Sale $sale)
    {
        /** @var Client $client */
        $client = $request->user('client');

        if ($sale->client_id !== $client->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return SaleResource::make($sale->loadMissing(['items', 'payments']));
    }
}
