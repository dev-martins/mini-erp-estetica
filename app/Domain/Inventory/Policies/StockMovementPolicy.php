<?php

namespace App\Domain\Inventory\Policies;

use App\Domain\Inventory\Models\StockMovement;
use App\Models\User;

class StockMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'reception'], true);
    }

    public function create(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function view(User $user, StockMovement $movement): bool
    {
        return $this->viewAny($user);
    }
}
