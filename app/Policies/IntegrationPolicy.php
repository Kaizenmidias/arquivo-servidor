<?php

namespace App\Policies;

use App\Models\User;

class IntegrationPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === 'admin' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function update(User $user): bool
    {
        return true;
    }
}
