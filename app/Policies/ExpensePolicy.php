<?php

namespace App\Policies;

use App\Models\{Expense, User};
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // tout le monde peut lister
    }

    public function view(User $user, Expense $expense): bool
    {
        return true; // lecture seule pour tous
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'project_manager']);
    }

    public function update(User $user, Expense $expense): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isChefProjet()) {
            return $expense->created_by === $user->id;
        }
        return false;
    }

    public function delete(User $user, Expense $expense): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isChefProjet()) {
            return $expense->created_by === $user->id;
        }
        return false;
    }
}
