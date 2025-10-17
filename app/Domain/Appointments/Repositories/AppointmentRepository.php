<?php

namespace App\Domain\Appointments\Repositories;

use App\Domain\Appointments\DTOs\AppointmentData;
use App\Domain\Appointments\Models\Appointment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class AppointmentRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Appointment::query()
            ->with([
                'client',
                'professional.user',
                'service',
                'clientPackage.package.service',
            ])
            ->when($filters['from'] ?? null, function (Builder $query, string $from): void {
                $query->where('scheduled_at', '>=', Carbon::parse($from));
            })
            ->when($filters['to'] ?? null, function (Builder $query, string $to): void {
                $query->where('scheduled_at', '<=', Carbon::parse($to));
            })
            ->when($filters['client_id'] ?? null, fn (Builder $query, $id) => $query->where('client_id', $id))
            ->when($filters['professional_id'] ?? null, fn (Builder $query, $id) => $query->where('professional_id', $id))
            ->orderBy('scheduled_at')
            ->paginate($perPage);
    }

    public function create(AppointmentData $data, array $extra = []): Appointment
    {
        return Appointment::create(array_merge($data->toArray(), $extra));
    }

    public function update(Appointment $appointment, AppointmentData $data, array $extra = []): Appointment
    {
        $appointment->update(array_merge($data->toArray(), $extra));

        return $appointment;
    }

    public function updateStatus(Appointment $appointment, string $status): Appointment
    {
        $appointment->update(['status' => $status]);

        return $appointment;
    }

    public function metricsForDate(CarbonInterface $date): array
    {
        /** @var \Illuminate\Support\Collection<int, \App\Domain\Appointments\Models\Appointment> $appointments */
        $appointments = Appointment::query()
            ->with(['service', 'professional'])
            ->whereDate('scheduled_at', $date->toDateString())
            ->get();

        if ($appointments->isEmpty()) {
            return [
                'date' => $date->toDateString(),
                'occupancy_rate' => 0.0,
                'no_show_rate' => 0.0,
                'avg_ticket' => 0.0,
                'total_appointments' => 0,
                'no_show_count' => 0,
            ];
        }

        $consideredStatuses = ['pending', 'confirmed', 'completed', 'no_show'];

        $relevant = $appointments->filter(
            fn (Appointment $appointment): bool => in_array($appointment->status, $consideredStatuses, true)
        );

        $totalRelevant = $relevant->count();

        $totalMinutes = $relevant->sum(function (Appointment $appointment) {
            return (int) ($appointment->duration_min ?: $appointment->service?->duration_min ?: 60);
        });

        $professionalCount = max(
            1,
            $relevant
                ->pluck('professional_id')
                ->filter()
                ->unique()
                ->count()
        );

        $capacityMinutes = $professionalCount * 8 * 60;
        $occupancyRate = $capacityMinutes > 0
            ? min(100, round(($totalMinutes / $capacityMinutes) * 100, 1))
            : 0.0;

        $noShowCount = $relevant->where('status', 'no_show')->count();
        $noShowRate = $totalRelevant > 0
            ? round(($noShowCount / $totalRelevant) * 100, 1)
            : 0.0;

        $ticketBase = $relevant->filter(fn (Appointment $appointment): bool => $appointment->service !== null);
        $avgTicket = $ticketBase->isNotEmpty()
            ? round($ticketBase->avg(fn (Appointment $appointment) => (float) $appointment->service->list_price), 2)
            : 0.0;

        return [
            'date' => $date->toDateString(),
            'occupancy_rate' => (float) $occupancyRate,
            'no_show_rate' => (float) $noShowRate,
            'avg_ticket' => (float) $avgTicket,
            'total_appointments' => $totalRelevant,
            'no_show_count' => $noShowCount,
        ];
    }
}
