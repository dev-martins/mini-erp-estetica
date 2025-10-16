<?php

namespace App\Domain\Appointments\DTOs;

use Illuminate\Http\Request;

class AppointmentData
{
    public function __construct(
        public readonly int $clientId,
        public readonly int $clientPackageId,
        public readonly int $professionalId,
        public readonly ?int $roomId,
        public readonly ?int $equipmentId,
        public readonly int $serviceId,
        public readonly string $scheduledAt,
        public readonly int $durationMin,
        public readonly string $status,
        public readonly ?string $source,
        public readonly ?string $notes,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            clientId: (int) $request->input('client_id'),
            clientPackageId: (int) $request->input('client_package_id'),
            professionalId: (int) $request->input('professional_id'),
            roomId: $request->input('room_id'),
            equipmentId: $request->input('equipment_id'),
            serviceId: (int) $request->input('service_id'),
            scheduledAt: (string) $request->input('scheduled_at'),
            durationMin: (int) $request->input('duration_min', 60),
            status: (string) $request->input('status', 'pending'),
            source: $request->input('source'),
            notes: $request->input('notes')
        );
    }

    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'client_package_id' => $this->clientPackageId,
            'professional_id' => $this->professionalId,
            'room_id' => $this->roomId,
            'equipment_id' => $this->equipmentId,
            'service_id' => $this->serviceId,
            'scheduled_at' => $this->scheduledAt,
            'duration_min' => $this->durationMin,
            'status' => $this->status,
            'source' => $this->source,
            'notes' => $this->notes,
        ];
    }
}
