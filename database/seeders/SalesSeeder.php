<?php

namespace Database\Seeders;

use App\Domain\Appointments\Models\Appointment;
use App\Domain\Appointments\Models\Professional;
use App\Domain\Sales\Models\Payment;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Models\SaleItem;
use App\Domain\Finance\Models\Commission;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'owner')->first();
        $appointments = Appointment::with(['client', 'service', 'professional'])->get();

        if ($appointments->isEmpty()) {
            $this->command?->warn('Nenhuma venda gerada: sem atendimentos cadastrados.');
            return;
        }

        $sales = collect();

        foreach ($appointments as $appointment) {
            $service = $appointment->service;
            $client = $appointment->client;
            $professional = $appointment->professional;

            $subtotal = $service->list_price ?? 200;
            $discount = $appointment->status === 'completed' ? 20 : 0;
            $total = $subtotal - $discount;

            $sale = Sale::updateOrCreate(
                [
                    'appointment_id' => $appointment->id,
                ],
                [
                    'client_id' => $client?->id,
                    'processed_by' => $admin?->id,
                    'sold_at' => $appointment->ended_at ?? now(),
                    'subtotal' => $subtotal,
                    'discount_total' => $discount,
                    'total_amount' => $total,
                    'channel' => 'pos',
                    'source' => $client?->source ?? 'Orgânico',
                    'payment_status' => 'paid',
                    'notes' => 'Venda gerada a partir do atendimento #' . $appointment->id,
                ]
            );

            SaleItem::updateOrCreate(
                [
                    'sale_id' => $sale->id,
                    'item_type' => 'service',
                    'item_id' => $service->id,
                ],
                [
                    'qty' => 1,
                    'unit_price' => $service->list_price ?? 0,
                    'discount' => $discount,
                    'total' => $total,
                ]
            );

            Payment::updateOrCreate(
                [
                    'sale_id' => $sale->id,
                    'method' => 'pix',
                ],
                [
                    'status' => 'paid',
                    'amount' => $total,
                    'paid_at' => $sale->sold_at,
                    'tx_ref' => 'PIX-' . str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT),
                    'meta' => [
                        'banco' => 'Banco Inter',
                        'chave' => 'financeiro@esteticaerp.com',
                    ],
                ]
            );

            if ($professional instanceof Professional) {
                Commission::updateOrCreate(
                    [
                        'professional_id' => $professional->id,
                        'sale_id' => $sale->id,
                    ],
                    [
                        'amount' => round($total * ($professional->commission_value / 100), 2),
                        'status' => 'pending',
                        'calculated_at' => now(),
                        'paid_at' => null,
                    ]
                );
            }

            $sales->push($sale);
        }

        $this->command?->info('Vendas registradas: ' . $sales->count());
    }
}