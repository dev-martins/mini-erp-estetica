<?php

namespace App\Domain\Inventory\Policies;

use App\Domain\Inventory\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'reception'], true);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->viewAny($user) || $user->role === 'professional';
    }

    public function create(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function update(User $user, Product $product): bool
    {
        return in_array($user->role, ['owner'], true);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->role === 'owner';
    }
}
