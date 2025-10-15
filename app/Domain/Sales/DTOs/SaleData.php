<?php

namespace App\Domain\Sales\DTOs;

use Illuminate\Http\Request;

class SaleData
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<int, array<string, mixed>> $payments
     */
    public function __construct(
        public readonly ?int $clientId,
        public readonly ?int $appointmentId,
        public readonly ?int $processedBy,
        public readonly string $soldAt,
        public readonly float $subtotal,
        public readonly float $discountTotal,
        public readonly float $totalAmount,
        public readonly string $channel,
        public readonly ?string $source,
        public readonly string $paymentStatus,
        public readonly ?string $notes,
        public readonly array $items,
        public readonly array $payments,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $items = array_map(fn ($item) => [
            'item_type' => $item['item_type'],
            'item_id' => $item['item_id'],
            'qty' => (float) $item['qty'],
            'unit_price' => (float) $item['unit_price'],
            'discount' => (float) ($item['discount'] ?? 0),
            'total' => (float) $item['total'],
        ], $request->input('items', []));

        $payments = array_map(fn ($payment) => [
            'method' => $payment['method'],
            'amount' => (float) $payment['amount'],
            'status' => $payment['status'] ?? 'paid',
            'paid_at' => $payment['paid_at'] ?? now()->toISOString(),
            'tx_ref' => $payment['tx_ref'] ?? null,
            'meta' => $payment['meta'] ?? [],
        ], $request->input('payments', []));

        return new self(
            clientId: $request->input('client_id'),
            appointmentId: $request->input('appointment_id'),
            processedBy: $request->user()?->id,
            soldAt: (string) $request->input('sold_at', now()->toISOString()),
            subtotal: (float) $request->input('subtotal', 0),
            discountTotal: (float) $request->input('discount_total', 0),
            totalAmount: (float) $request->input('total_amount', 0),
            channel: (string) $request->input('channel', 'pos'),
            source: $request->input('source'),
            paymentStatus: (string) $request->input('payment_status', 'paid'),
            notes: $request->input('notes'),
            items: $items,
            payments: $payments,
        );
    }
}
