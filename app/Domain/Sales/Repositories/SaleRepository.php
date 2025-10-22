<?php

namespace App\Domain\Sales\Repositories;

use App\Domain\Sales\DTOs\SaleData;
use App\Domain\Sales\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SaleRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['client', 'items', 'payments'])
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('sold_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('sold_at', '<=', $to))
            ->when(
                $filters['client_id'] ?? null,
                fn ($query, $clientId) => $query->where('client_id', $clientId)
            )
            ->orderByDesc('sold_at')
            ->paginate($perPage);
    }

    public function summary(array $filters = []): array
    {
        $baseQuery = Sale::query()
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('sold_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('sold_at', '<=', $to))
            ->when(
                $filters['client_id'] ?? null,
                fn ($query, $clientId) => $query->where('client_id', $clientId)
            );

        $total = (clone $baseQuery)->sum('total_amount');
        $count = (clone $baseQuery)->count();

        $mixTotals = Sale::query()
            ->selectRaw('sale_items.item_type as type, SUM(sale_items.total) as value')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('sales.sold_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('sales.sold_at', '<=', $to))
            ->when(
                $filters['client_id'] ?? null,
                fn ($query, $clientId) => $query->where('sales.client_id', $clientId)
            )
            ->groupBy('sale_items.item_type')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->type => (float) $row->value])
            ->all();

        $mixTotals = [
            'service' => $mixTotals['service'] ?? 0.0,
            'product' => $mixTotals['product'] ?? 0.0,
            'package' => $mixTotals['package'] ?? 0.0,
        ];

        $grandTotal = array_sum($mixTotals);

        $mixPercentages = $grandTotal > 0
            ? array_map(fn ($value) => (float) round(($value / $grandTotal) * 100, 1), $mixTotals)
            : [
                'service' => 0.0,
                'product' => 0.0,
                'package' => 0.0,
            ];

        return [
            'total' => (float) round($total, 2),
            'count' => (int) $count,
            'average_ticket' => $count > 0 ? (float) round($total / $count, 2) : 0.0,
            'commission_estimated' => (float) round($total * 0.25, 2),
            'mix' => [
                'services' => $mixPercentages['service'],
                'products' => $mixPercentages['product'],
                'packages' => $mixPercentages['package'],
            ],
            'mix_totals' => [
                'services' => $mixTotals['service'],
                'products' => $mixTotals['product'],
                'packages' => $mixTotals['package'],
            ],
        ];
    }

    public function create(SaleData $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::create([
                'client_id' => $data->clientId,
                'appointment_id' => $data->appointmentId,
                'processed_by' => $data->processedBy,
                'sold_at' => $data->soldAt,
                'subtotal' => $data->subtotal,
                'discount_total' => $data->discountTotal,
                'total_amount' => $data->totalAmount,
                'channel' => $data->channel,
                'source' => $data->source,
                'payment_status' => $data->paymentStatus,
                'notes' => $data->notes,
            ]);

            foreach ($data->items as $item) {
                $sale->items()->create($item);
            }

            foreach ($data->payments as $payment) {
                $sale->payments()->create($payment);
            }

            return $sale->load(['client', 'items', 'payments']);
        });
    }
}
