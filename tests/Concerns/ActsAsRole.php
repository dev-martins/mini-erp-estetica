<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\User;
use Laravel\Passport\Passport;

trait ActsAsRole
{
    /**
     * Create and authenticate a user for a given domain role.
     */
    protected function actingAsRole(string $role, array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => $role,
        ], $overrides));

        Passport::actingAs($user);

        return $user;
    }
}
