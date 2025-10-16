<?php

namespace App\Domain\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Appointments\Models\Appointment */
class AppointmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'full_name' => $this->client->full_name,
                'phone' => $this->client->phone,
            ]),
            'professional' => $this->whenLoaded('professional', fn () => [
                'id' => $this->professional->id,
                'display_name' => $this->professional->display_name,
            ]),
            'service' => $this->whenLoaded('service', fn () => [
                'id' => $this->service->id,
                'name' => $this->service->name,
            ]),
            'client_package' => $this->whenLoaded('clientPackage', fn () => [
                'id' => $this->clientPackage->id,
                'remaining_sessions' => $this->clientPackage->remaining_sessions,
                'expiry_at' => $this->clientPackage->expiry_at?->toAtomString(),
                'package' => [
                    'id' => $this->clientPackage->package->id,
                    'name' => $this->clientPackage->package->name,
                    'sessions_count' => $this->clientPackage->package->sessions_count,
                ],
            ]),
            'room_id' => $this->room_id,
            'equipment_id' => $this->equipment_id,
            'scheduled_at' => $this->scheduled_at?->toAtomString(),
            'duration_min' => $this->duration_min,
            'status' => $this->status,
            'source' => $this->source,
            'notes' => $this->notes,
            'package_session_number' => $this->package_session_number,
            'started_at' => $this->started_at?->toAtomString(),
            'ended_at' => $this->ended_at?->toAtomString(),
            'created_at' => $this->created_at?->toAtomString(),
        ];
    }
}
