<?php

namespace App\Domain\Appointments\Services;

use App\Domain\Appointments\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentAttendanceReminder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class AttendanceAlertService
{
    public function dispatch(): int
    {
        $threshold = CarbonImmutable::now()->subDay();

        /** @var \Illuminate\Support\Collection<int, \App\Domain\Appointments\Models\Appointment> $appointments */
        $appointments = Appointment::query()
            ->with(['client', 'professional.user'])
            ->whereNull('attendance_alerted_at')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_at', '<=', $threshold)
            ->get();

        if ($appointments->isEmpty()) {
            return 0;
        }

        /** @var \Illuminate\Support\Collection<int, \App\Models\User> $baseRecipients */
        $baseRecipients = User::query()
            ->whereIn('role', ['owner', 'reception'])
            ->get();

        $sent = 0;

        foreach ($appointments as $appointment) {
            $recipients = $this->resolveRecipients($baseRecipients, $appointment);

            if ($recipients->isEmpty()) {
                continue;
            }

            Notification::send($recipients, new AppointmentAttendanceReminder($appointment));

            $appointment->forceFill([
                'attendance_alerted_at' => CarbonImmutable::now(),
            ])->save();

            $sent++;
        }

        return $sent;
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\User> $baseRecipients
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    private function resolveRecipients(Collection $baseRecipients, Appointment $appointment): Collection
    {
        $recipients = collect($baseRecipients->all());

        $professionalUser = $appointment->professional?->user;

        if ($professionalUser instanceof User) {
            $recipients->push($professionalUser);
        }

        return $recipients->unique('id')->values();
    }
}
