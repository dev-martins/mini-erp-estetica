<?php

namespace App\Domain\Sales\Policies;

use App\Domain\Sales\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'reception'], true);
    }

    public function view(User $user, Sale $sale): bool
    {
        if ($user->role === 'professional') {
            return $sale->appointment?->professional?->user_id === $user->id;
        }

        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'reception'], true);
    }

    public function update(User $user, Sale $sale): bool
    {
        return in_array($user->role, ['owner'], true);
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->role === 'owner';
    }
}
