<?php

namespace App\Domain\Sales\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Sales\Models\Sale */
class SaleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'full_name' => $this->client->full_name,
            ]),
            'sold_at' => $this->sold_at?->toAtomString(),
            'subtotal' => (float) $this->subtotal,
            'discount_total' => (float) $this->discount_total,
            'total_amount' => (float) $this->total_amount,
            'channel' => $this->channel,
            'source' => $this->source,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'item_type' => $item->item_type,
                'item_id' => $item->item_id,
                'qty' => (float) $item->qty,
                'unit_price' => (float) $item->unit_price,
                'discount' => (float) $item->discount,
                'total' => (float) $item->total,
            ])),
            'payments' => $this->whenLoaded('payments', fn () => $this->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'method' => $payment->method,
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at?->toAtomString(),
            ])),
            'created_at' => $this->created_at?->toAtomString(),
        ];
    }
}
