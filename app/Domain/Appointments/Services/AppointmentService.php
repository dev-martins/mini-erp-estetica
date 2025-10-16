<?php

namespace App\Domain\Appointments\Services;

use App\Domain\Appointments\DTOs\AppointmentData;
use App\Domain\Appointments\Models\Appointment;
use App\Domain\Appointments\Repositories\AppointmentRepository;
use App\Domain\Inventory\DTOs\StockMovementData;
use App\Domain\Inventory\Services\StockMovementService;
use App\Domain\Sales\Models\ClientPackage;
use App\Domain\Sales\Services\ClientPackageService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    public function __construct(
        private readonly AppointmentRepository $repository,
        private readonly ClientPackageService $packages,
        private readonly StockMovementService $stockMovements
    ) {
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function create(AppointmentData $data): Appointment
    {
        return DB::transaction(function () use ($data): Appointment {
            $scheduledAt = CarbonImmutable::parse($data->scheduledAt);

            [$clientPackage, $sessionNumber] = $this->packages->reserveForAppointment(
                $data->clientPackageId,
                $data->clientId,
                $data->serviceId,
                $scheduledAt
            );

            $this->assertProfessionalAvailability($data, $scheduledAt);

            $this->assertMinInterval($data, $scheduledAt, $clientPackage);

            $service = $clientPackage->package?->service;

            if (! $service) {
                throw ValidationException::withMessages([
                    'service_id' => 'Servico associado ao pacote nao foi encontrado.',
                ]);
            }

            $this->packages->ensureServiceStock($service);

            $appointment = $this->repository->create(
                $data,
                [
                    'scheduled_at' => $scheduledAt,
                    'package_session_number' => $sessionNumber,
                ]
            );

            return $appointment->load([
                'client',
                'professional.user',
                'service',
                'clientPackage.package',
            ]);
        });
    }

    public function update(Appointment $appointment, AppointmentData $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data): Appointment {
            if ($appointment->client_package_id !== $data->clientPackageId) {
                throw ValidationException::withMessages([
                    'client_package_id' => 'Nao e possivel alterar o pacote vinculado ao agendamento.',
                ]);
            }

            if ($appointment->service_id !== $data->serviceId) {
                throw ValidationException::withMessages([
                    'service_id' => 'Nao e possivel alterar o servico vinculado ao agendamento.',
                ]);
            }

            $scheduledAt = CarbonImmutable::parse($data->scheduledAt);

            $this->assertProfessionalAvailability($data, $scheduledAt, $appointment);

            $clientPackage = $appointment->clientPackage()->with('package.service')->first();
            if ($clientPackage && $clientPackage->expiry_at && $scheduledAt->greaterThan($clientPackage->expiry_at)) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'A nova data ultrapassa a validade do pacote.',
                ]);
            }

            if ($clientPackage) {
                $this->assertMinInterval($data, $scheduledAt, $clientPackage, $appointment);
            }

            $updated = $this->repository->update($appointment, $data, [
                'scheduled_at' => $scheduledAt,
            ]);

            return $updated->load([
                'client',
                'professional.user',
                'service',
                'clientPackage.package',
            ]);
        });
    }

    public function setStatus(Appointment $appointment, string $status): Appointment
    {
        return DB::transaction(function () use ($appointment, $status): Appointment {
            $originalStatus = $appointment->status;

            if ($status === $originalStatus) {
                return $appointment;
            }

            if ($originalStatus === 'completed' && $status !== 'completed') {
                throw ValidationException::withMessages([
                    'status' => 'Nao e possivel alterar um atendimento ja concluido.',
                ]);
            }

            $clientPackage = $appointment->client_package_id
                ? ClientPackage::query()->find($appointment->client_package_id)
                : null;

            if (in_array($status, ['cancelled', 'no_show'], true)
                && in_array($originalStatus, ['pending', 'confirmed'], true)
                && $clientPackage
            ) {
                $this->packages->releaseSession($clientPackage);
            }

            if (in_array($status, ['pending', 'confirmed'], true)
                && in_array($originalStatus, ['cancelled', 'no_show'], true)
                && $clientPackage
            ) {
                $this->packages->reReserveSession($clientPackage);
            }

            $updated = $this->repository->updateStatus($appointment, $status)->load([
                'client',
                'professional.user',
                'service.kit.items.product',
                'sessionItems',
                'clientPackage.package.service',
            ]);

            if ($status === 'completed') {
                $this->consumeKitStock($updated);
            }

            return $updated;
        });
    }

    private function consumeKitStock(Appointment $appointment): void
    {
        $kit = $appointment->service?->kit;

        if (! $kit || $kit->items->isEmpty()) {
            return;
        }

        if ($appointment->sessionItems()->exists()) {
            // Ja houve baixa automatica registrada.
            return;
        }

        foreach ($kit->items as $item) {
            if (! $item->product) {
                continue;
            }

            $appointment->sessionItems()->create([
                'product_id' => $item->product_id,
                'quantity_used' => $item->qty_per_session,
            ]);

            $this->stockMovements->register(new StockMovementData(
                productId: $item->product_id,
                batchId: null,
                type: 'out',
                qty: (float) $item->qty_per_session,
                unitCost: $item->product->cost_per_unit,
                reason: 'session_consumption',
                meta: [
                    'appointment_id' => $appointment->id,
                    'service_id' => $appointment->service_id,
                    'client_id' => $appointment->client_id,
                ]
            ));
        }
    }

    private function assertProfessionalAvailability(
        AppointmentData $data,
        CarbonImmutable $scheduledAt,
        ?Appointment $ignore = null
    ): void {
        $start = $scheduledAt;
        $end = $scheduledAt->addMinutes($data->durationMin);

        $hasConflict = Appointment::query()
            ->where('professional_id', $data->professionalId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($ignore, fn ($query) => $query->where('id', '!=', $ignore->id))
            ->where('scheduled_at', '<', $end)
            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_min MINUTE) > ?', [$start->toDateTimeString()])
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'O profissional selecionado ja possui um atendimento neste horario.',
            ]);
        }
    }

    private function assertMinInterval(
        AppointmentData $data,
        CarbonImmutable $scheduledAt,
        ClientPackage $clientPackage,
        ?Appointment $ignore = null
    ): void {
        $packageInterval = $clientPackage->package?->min_interval_hours;
        $serviceInterval = $clientPackage->package?->service?->min_interval_hours;
        $minIntervalHours = $packageInterval ?? $serviceInterval ?? 0;

        if ($minIntervalHours <= 0) {
            return;
        }

        $windowStart = $scheduledAt->subHours($minIntervalHours);
        $windowEnd = $scheduledAt->addHours($minIntervalHours);

        $nearbyAppointments = Appointment::query()
            ->where('client_id', $data->clientId)
            ->where('service_id', $data->serviceId)
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->when($ignore, fn ($query) => $query->where('id', '!=', $ignore->id))
            ->get(['id', 'scheduled_at']);

        foreach ($nearbyAppointments as $nearby) {
            $diffMinutes = $nearby->scheduled_at?->diffInMinutes($scheduledAt, true) ?? 0;

            if ($diffMinutes < $minIntervalHours * 60) {
                throw ValidationException::withMessages([
                    'scheduled_at' => sprintf(
                        'Apenas agendamentos com intervalo minimo de %d hora(s) sao permitidos para este tratamento.',
                        $minIntervalHours
                    ),
                ]);
            }
        }
    }
}
