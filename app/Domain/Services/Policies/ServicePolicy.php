<?php

namespace App\Domain\Services\Policies;

use App\Domain\Services\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'reception', 'professional'], true);
    }

    public function view(User $user, Service $service): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['owner'], true);
    }

    public function update(User $user, Service $service): bool
    {
        return in_array($user->role, ['owner'], true);
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->role === 'owner';
    }
}
