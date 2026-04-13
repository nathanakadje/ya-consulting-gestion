<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /** Gérer les utilisateurs — admin seulement */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }
    public function update(User $user): bool
    {
        return $user->isAdmin();
    }
    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }
}
