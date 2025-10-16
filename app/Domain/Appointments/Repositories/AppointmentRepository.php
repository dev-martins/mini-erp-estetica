<?php

namespace App\Domain\Appointments\Repositories;

use App\Domain\Appointments\DTOs\AppointmentData;
use App\Domain\Appointments\Models\Appointment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
}
