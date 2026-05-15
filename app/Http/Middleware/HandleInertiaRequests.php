<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Project;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // return [
        //     ...parent::share($request),
        //     //
        // ];
        $user = $request->user();

        return array_merge(parent::share($request), [

            'auth' => [
                'user' => $user ? [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'role'   => $user->role,
                    'theme'  => $user->theme,
                    'avatar' => $user->profile_photo_url ?? $user->avatar,

                    // ── Permissions granulaires ──────────────────────────────
                    // Ces props sont disponibles dans tous les composants Vue
                    // via $page.props.auth.user.can.xxx
                    'can' => [
                        // Projets
                        'create_project'  => in_array($user->role, ['admin', 'project_manager']),
                        'edit_project'    => in_array($user->role, ['admin', 'project_manager']),
                        'delete_project'  => $user->role === 'admin',

                        // Dépenses
                        'create_expense'  => in_array($user->role, ['admin', 'project_manager']),
                        'edit_expense'    => in_array($user->role, ['admin', 'project_manager']),
                        'delete_expense'  => in_array($user->role, ['admin', 'project_manager']),

                        // Rapports
                        'view_reports'    => in_array($user->role, ['admin', 'project_manager']),
                        'export_reports'  => in_array($user->role, ['admin', 'project_manager']),

                        // Équipe / utilisateurs
                        'manage_users'    => $user->role === 'admin',

                        // Alias pratique côté Vue
                        'manage_projects' => in_array($user->role, ['admin', 'project_manager']),
                    ],

                    // Navigation adaptée au rôle
                    'nav' => $this->navForRole($user->role),
                ] : null,
            ],

            'theme' => $user?->theme ?? 'light',

            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
                'info'    => fn() => $request->session()->get('info'),
            ],
        ]);
    }

    /**
     * Retourne les éléments de navigation selon le rôle.
     * Le frontend utilise ces données pour construire la sidebar
     * plutôt que de hardcoder les items.
     */
    private function navForRole(string $role): array
    {
        $all = [
            ['label' => 'Dashboard', 'icon' => 'dashboard',    'route' => '/gestion/dashboard', 
'exact' => true],
            ['label' => 'Projets',   'icon' => 'account_tree', 'route' => '/gestion/projects'],
            ['label' => 'Dépenses',  'icon' => 'receipt_long', 'route' => '/gestion/expenses'],
            ['label' => 'Rapports',  'icon' => 'bar_chart',    'route' => '/gestion/reports',   
'roles' => ['admin', 'project_manager']],
            ['label' => 'Équipe',    'icon' => 'groups',        'route' => '/gestion/team',      
'roles' => ['admin']],
        ];

        return array_values(array_filter($all, function ($item) use ($role) {
            // Si l'item a une restriction de rôle, vérifier
            if (isset($item['roles'])) {
                return in_array($role, $item['roles']);
            }
            return true; // item accessible à tous
        }));
    }
}
