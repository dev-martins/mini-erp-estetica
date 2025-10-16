<?php

namespace Database\Seeders;

use App\Domain\Appointments\Models\Appointment;
use App\Domain\Sales\Models\ClientPackage;
use App\Domain\Sales\Models\Package;
use App\Domain\Sales\Models\Sale;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = Appointment::query()
            ->with(['client', 'service'])
            ->orderBy('scheduled_at')
            ->get();

        if ($appointments->isEmpty()) {
            $this->command?->warn('Pacotes não gerados: nenhum agendamento encontrado.');
            return;
        }

        $packagesByService = [];
        $sessionsPerPackage = 5;
        $defaultExpiryDays = 180;

        foreach ($appointments->groupBy('service_id') as $serviceId => $serviceAppointments) {
            $service = $serviceAppointments->first()?->service;

            if (! $service) {
                $this->command?->warn('Serviço não encontrado para gerar pacote.');
                continue;
            }

            $packageName = sprintf('Plano Fidelidade - %s', $service->name);
            $basePrice = (float) ($service->list_price ?? 0);
            $packagePrice = $basePrice > 0 ? round($basePrice * $sessionsPerPackage * 0.9, 2) : 0;

            $packagesByService[$serviceId] = Package::updateOrCreate(
                [
                    'service_id' => $serviceId,
                    'name' => $packageName,
                ],
                [
                    'sessions_count' => $sessionsPerPackage,
                    'min_interval_hours' => $service->min_interval_hours ?: null,
                    'price' => $packagePrice ?: ($basePrice ?: 0),
                    'expiry_days' => $defaultExpiryDays,
                    'description' => sprintf(
                        'Pacote com %d sessões de %s para fidelização.',
                        $sessionsPerPackage,
                        mb_strtolower($service->name)
                    ),
                    'active' => true,
                ]
            );
        }

        if (empty($packagesByService)) {
            $this->command?->warn('Pacotes não gerados: serviços indisponíveis.');
            return;
        }

        $appointments
            ->groupBy('client_id')
            ->each(function ($clientAppointments) use ($packagesByService, $sessionsPerPackage, $defaultExpiryDays): void {
                $clientAppointments
                    ->groupBy('service_id')
                    ->each(function ($serviceAppointments, $serviceId) use (
                        $packagesByService,
                        $sessionsPerPackage,
                        $defaultExpiryDays
                    ): void {
                        $package = $packagesByService[$serviceId] ?? null;

                        if (! $package) {
                            return;
                        }

                        $sale = Sale::query()
                            ->whereIn('appointment_id', $serviceAppointments->pluck('id'))
                            ->orderBy('sold_at')
                            ->first();

                        if (! $sale || ! $sale->client) {
                            return;
                        }

                        $consumedSessions = min($serviceAppointments->count(), $sessionsPerPackage);
                        $remainingSessions = max($sessionsPerPackage - $consumedSessions, 0);
                        $purchasedAt = $sale->sold_at ?? now();

                        $clientPackage = ClientPackage::updateOrCreate(
                            [
                                'client_id' => $sale->client_id,
                                'package_id' => $package->id,
                            ],
                            [
                                'sale_id' => $sale->id,
                                'purchased_at' => $purchasedAt,
                                'remaining_sessions' => $remainingSessions,
                                'expiry_at' => Carbon::parse($purchasedAt)->addDays($package->expiry_days ?? $defaultExpiryDays),
                            ]
                        );

                        $serviceAppointments
                            ->sortBy('scheduled_at')
                            ->values()
                            ->each(function (Appointment $appointment, int $index) use ($clientPackage): void {
                                $appointment->forceFill([
                                    'client_package_id' => $clientPackage->id,
                                    'package_session_number' => $index + 1,
                                ])->save();
                            });
                    });
            });

        $this->command?->info('Pacotes gerados para clientes com agendamentos ativos.');
    }
}
