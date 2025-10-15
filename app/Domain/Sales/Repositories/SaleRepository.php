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
            ->orderByDesc('sold_at')
            ->paginate($perPage);
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
