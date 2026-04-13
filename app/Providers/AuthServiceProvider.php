<?php

namespace App\Providers;

use App\Models\{Expense, Project, User};
use App\Policies\{ExpensePolicy, ProjectPolicy, ReportPolicy, UserPolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Expense::class => ExpensePolicy::class,
        User::class    => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Gate globale pour les rapports (pas un Model)
        \Illuminate\Support\Facades\Gate::define('view-reports', function (User $user) {
            return in_array($user->role, ['admin', 'project_manager']);
        });

        \Illuminate\Support\Facades\Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        \Illuminate\Support\Facades\Gate::define('export-reports', function (User $user) {
            return in_array($user->role, ['admin', 'project_manager']);
        });
    }
}
