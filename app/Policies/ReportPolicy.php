<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return in_array($user->role, ['admin', 'project_manager']);
    }

    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'project_manager']);
    }
}
