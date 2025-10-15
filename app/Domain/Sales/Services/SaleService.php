<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\DTOs\SaleData;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Repositories\SaleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SaleService
{
    public function __construct(private readonly SaleRepository $repository)
    {
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function create(SaleData $data): Sale
    {
        $items = collect($data->items)->map(function (array $item) {
            $subtotal = (float) $item['unit_price'] * (float) $item['qty'];
            $discount = (float) ($item['discount'] ?? 0);
            $total = $subtotal - $discount;

            return [
                'item_type' => $item['item_type'],
                'item_id' => $item['item_id'],
                'qty' => (float) $item['qty'],
                'unit_price' => (float) $item['unit_price'],
                'discount' => $discount,
                'total' => $total,
            ];
        });

        $subtotal = $items->sum(fn ($item) => (float) $item['unit_price'] * (float) $item['qty']);
        $discount = $items->sum(fn ($item) => (float) $item['discount']);
        $total = $items->sum(fn ($item) => (float) $item['total']);

        $hydrated = new SaleData(
            clientId: $data->clientId,
            appointmentId: $data->appointmentId,
            processedBy: $data->processedBy,
            soldAt: $data->soldAt,
            subtotal: $subtotal,
            discountTotal: $discount,
            totalAmount: $total,
            channel: $data->channel,
            source: $data->source,
            paymentStatus: $data->paymentStatus,
            notes: $data->notes,
            items: $items->toArray(),
            payments: $data->payments,
        );

        return $this->repository->create($hydrated);
    }
}
