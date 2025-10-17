<?php

namespace App\Notifications;

use App\Domain\Appointments\Models\Appointment;
use Illuminate\Notifications\Notification;

class AppointmentAttendanceReminder extends Notification
{
    public function __construct(private readonly Appointment $appointment)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $scheduledAt = $this->appointment->scheduled_at?->setTimezone(config('app.timezone'));

        return [
            'type' => 'appointment_attendance_pending',
            'appointment_id' => $this->appointment->id,
            'client_id' => $this->appointment->client_id,
            'client_name' => $this->appointment->client?->full_name,
            'professional_id' => $this->appointment->professional_id,
            'scheduled_at' => $scheduledAt?->toIso8601String(),
            'message' => sprintf(
                'Informe o comparecimento do cliente %s no atendimento marcado para %s.',
                $this->appointment->client?->full_name ?? 'desconhecido',
                $scheduledAt?->format('d/m/Y H:i') ?? 'data indefinida'
            ),
        ];
    }
}
