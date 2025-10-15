<?php

namespace App\Domain\Appointments\Services;

use App\Domain\Appointments\DTOs\AppointmentData;
use App\Domain\Appointments\Models\Appointment;
use App\Domain\Appointments\Repositories\AppointmentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AppointmentService
{
    public function __construct(private readonly AppointmentRepository $repository)
    {
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function create(AppointmentData $data): Appointment
    {
        return $this->repository->create($data);
    }

    public function update(Appointment $appointment, AppointmentData $data): Appointment
    {
        return $this->repository->update($appointment, $data);
    }

    public function setStatus(Appointment $appointment, string $status): Appointment
    {
        return $this->repository->updateStatus($appointment, $status);
    }
}
