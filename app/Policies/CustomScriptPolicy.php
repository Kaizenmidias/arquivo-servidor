<?php

namespace App\Policies;

use App\Models\User;

class CustomScriptPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === 'admin' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user): bool
    {
        return true;
    }

    public function delete(User $user): bool
    {
        return true;
    }

    public function duplicate(User $user): bool
    {
        return true;
    }

    public function toggle(User $user): bool
    {
        return true;
    }
}
