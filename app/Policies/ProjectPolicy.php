<?php

namespace App\Policies;

use App\Models\{Project, User};
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /** Lister les projets — tout le monde */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Voir un projet */
    public function view(User $user, Project $project): bool
    {
        // Admin voit tout
        if ($user->isAdmin()) return true;

        // Chef de projet voit ses projets
        if ($user->isChefProjet()) {
            return $project->created_by === $user->id
                || $project->project_lead_id === $user->id;
        }

        // Collaborateur : lecture seule sur tous les projets
        return true;
    }

    /** Créer un projet */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'project_manager']);
    }

    /** Modifier un projet */
    public function update(User $user, Project $project): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isChefProjet()) {
            return $project->created_by === $user->id
                || $project->project_lead_id === $user->id;
        }

        return false; // collaborateur : lecture seule
    }

    /** Supprimer un projet — admin seulement */
    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }
}
