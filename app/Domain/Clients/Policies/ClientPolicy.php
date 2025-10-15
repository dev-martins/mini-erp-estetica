<?php

namespace App\Domain\Clients\Policies;

use App\Domain\Clients\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'reception', 'professional'], true);
    }

    public function view(User $user, Client $client): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'reception'], true);
    }

    public function update(User $user, Client $client): bool
    {
        return in_array($user->role, ['owner', 'reception'], true);
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->role === 'owner';
    }
}
