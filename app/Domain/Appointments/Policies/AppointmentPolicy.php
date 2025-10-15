<?php

namespace App\Domain\Appointments\Policies;

use App\Domain\Appointments\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'reception', 'professional'], true);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->role === 'professional') {
            return $appointment->professional?->user_id === $user->id;
        }

        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'reception'], true);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->role === 'professional') {
            return $appointment->professional?->user_id === $user->id;
        }

        return in_array($user->role, ['owner', 'reception'], true);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['owner'], true);
    }
}
