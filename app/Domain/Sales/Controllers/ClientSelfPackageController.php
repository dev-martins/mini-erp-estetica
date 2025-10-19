<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Sales\Models\ClientPackage;
use App\Domain\Sales\Models\Package;
use App\Domain\Sales\Requests\ClientPackageSubscribeRequest;
use App\Domain\Sales\Resources\ClientPackageResource;
use App\Domain\Sales\Resources\PackageResource;
use App\Domain\Sales\Services\SaleService;
use App\Domain\Sales\DTOs\SaleData;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientSelfPackageController extends Controller
{
    public function __construct(private readonly SaleService $sales)
    {
    }

    public function index(Request $request)
    {
        $client = $request->user('client');

        $packages = ClientPackage::query()
            ->with(['package.service'])
            ->where('client_id', $client->id)
            ->when(
                ! $request->boolean('include_expired', false),
                fn ($query) => $query->where(function ($builder) {
                    $builder->whereNull('expiry_at')
                        ->orWhere('expiry_at', '>=', now());
                })
            )
            ->orderByDesc('remaining_sessions')
            ->orderBy('expiry_at')
            ->get();

        return ClientPackageResource::collection($packages);
    }

    public function available(Request $request)
    {
        $serviceId = $request->integer('service_id');

        $packages = Package::query()
            ->with('service')
            ->where('active', true)
            ->when($serviceId, fn ($query) => $query->where('service_id', $serviceId))
            ->orderBy('service_id')
            ->orderBy('price')
            ->get();

        return PackageResource::collection($packages);
    }

    public function subscribe(ClientPackageSubscribeRequest $request)
    {
        $client = $request->user('client');

        /** @var Package $package */
        $package = Package::query()
            ->with('service')
            ->where('active', true)
            ->findOrFail($request->integer('package_id'));

        $price = (float) ($package->price ?? 0);

        $sale = $this->sales->create(new SaleData(
            clientId: $client->id,
            appointmentId: null,
            processedBy: null,
            soldAt: now()->toIso8601String(),
            subtotal: $price,
            discountTotal: 0,
            totalAmount: $price,
            channel: 'online',
            source: 'client_app',
            paymentStatus: 'pending',
            notes: $request->input('notes'),
            items: [[
                'item_type' => 'package',
                'item_id' => $package->id,
                'qty' => 1,
                'unit_price' => $price,
                'discount' => 0,
                'total' => $price,
            ]],
            payments: $this->buildPaymentsPayload($request, $price)
        ));

        $expiryAt = $package->expiry_days
            ? CarbonImmutable::now()->addDays((int) $package->expiry_days)
            : null;

        $sessionsCount = $package->sessions_count ?: 1;

        $clientPackage = ClientPackage::create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'sale_id' => $sale->id,
            'purchased_at' => now(),
            'remaining_sessions' => $sessionsCount,
            'expiry_at' => $expiryAt,
        ])->load(['package.service']);

        return response()->json([
            'message' => 'Pacote assinado com sucesso. Confirme o pagamento com a clínica para liberar o agendamento.',
            'client_package' => ClientPackageResource::make($clientPackage),
        ], Response::HTTP_CREATED);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildPaymentsPayload(Request $request, float $price): array
    {
        $method = $request->input('payment_method');
        if (! $method) {
            return [];
        }

        return [[
            'method' => $method,
            'amount' => $price,
            'status' => 'pending',
            'paid_at' => now()->toIso8601String(),
            'tx_ref' => null,
            'meta' => [
                'origin' => 'client_app',
            ],
        ]];
    }
}
