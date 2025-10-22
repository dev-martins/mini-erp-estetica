<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\DTOs\SaleData;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Repositories\SaleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class SaleService
{
    public function __construct(private readonly SaleRepository $repository)
    {
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $resolvedFilters = $this->applyRangeFilter($filters);

        return $this->repository->paginate($resolvedFilters, $perPage);
    }

    public function listWithSummary(array $filters = [], int $perPage = 15): array
    {
        $resolvedFilters = $this->applyRangeFilter($filters);

        $paginator = $this->repository->paginate($resolvedFilters, $perPage);
        $summary = $this->repository->summary($resolvedFilters);

        return [
            'paginator' => $paginator,
            'summary' => array_merge($summary, $this->rangeMetadata($filters, $resolvedFilters)),
        ];
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

    private function applyRangeFilter(array $filters): array
    {
        $resolved = $filters;

        if (!empty($filters['range']) && empty($filters['from']) && empty($filters['to'])) {
            [$from, $to] = $this->resolveDateRange($filters['range']);
            $resolved['from'] = $from;
            $resolved['to'] = $to;
        }

        unset($resolved['range']);

        return $resolved;
    }

    private function resolveDateRange(string $range): array
    {
        $now = Carbon::now();

        return match ($range) {
            'day' => [
                $now->copy()->startOfDay()->toDateString(),
                $now->copy()->endOfDay()->toDateString(),
            ],
            'week' => [
                $now->copy()->startOfWeek()->toDateString(),
                $now->copy()->endOfWeek()->toDateString(),
            ],
            'month' => [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ],
            'year' => [
                $now->copy()->startOfYear()->toDateString(),
                $now->copy()->endOfYear()->toDateString(),
            ],
            'recent' => [
                $now->copy()->subDays(30)->toDateString(),
                $now->toDateString(),
            ],
            default => [null, null],
        };
    }

    private function rangeMetadata(array $originalFilters, array $resolvedFilters): array
    {
        $rangeKey = $originalFilters['range'] ?? null;

        return [
            'range' => $rangeKey ?? 'custom',
            'range_label' => $this->labelForRange($rangeKey),
            'period' => [
                'from' => $resolvedFilters['from'] ?? null,
                'to' => $resolvedFilters['to'] ?? null,
            ],
        ];
    }

    private function labelForRange(?string $range): string
    {
        return match ($range) {
            'day' => 'Hoje',
            'week' => 'Semana',
            'month' => "M\u{00EA}s",
            'year' => 'Ano',
            'recent' => 'Recentes',
            default => "Per\u{00ED}odo selecionado",
        };
    }
}
