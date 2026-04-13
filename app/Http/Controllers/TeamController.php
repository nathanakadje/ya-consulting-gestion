<?php
// ============================================================
// app/Http/Controllers/TeamController.php
// Gestion des utilisateurs — Admin seulement
// ============================================================
namespace App\Http\Controllers;

use App\Models\{ActivityLog, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    // public function __construct()
    // {
    //     // Double sécurité : middleware route + vérification explicite
    //     $this->middleware(function ($request, $next) {
    //         if (!$request->user()?->isAdmin()) {
    //             abort(403, 'Accès réservé aux administrateurs.');
    //         }
    //         return $next($request);
    //     });
    // }

    // ── Liste des membres ──────────────────────────────────
    public function index(): Response
    {
        $users = User::withCount(['projects', 'expenses'])
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'              => $u->id,
                'name'            => $u->name,
                'email'           => $u->email,
                'role'            => $u->role,
                'theme'           => $u->theme,
                'avatar'          => $u->profile_photo_url ?? $u->avatar,
                'projects_count'  => $u->projects_count,
                'expenses_count'  => $u->expenses_count,
                'created_at'      => $u->created_at?->format('d/m/Y'),
                'email_verified'  => !is_null($u->email_verified_at),
            ]);

        $stats = [
            'total'         => $users->count(),
            'admins'        => $users->where('role', 'admin')->count(),
            'chefs'         => $users->where('role', 'project_manager')->count(),
            'collaborateurs' => $users->where('role', 'staff_member')->count(),
        ];

        return Inertia::render('Team/Index', [
            'members' => $users,
            'stats'   => $stats,
        ]);
    }

    // ── Formulaire création ────────────────────────────────
    public function create(): Response
    {
        return Inertia::render('Team/Form', ['member' => null]);
    }

    // ── Créer un utilisateur ───────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:admin,project_manager,staff_member',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.unique'          => 'Cet email est déjà utilisé.',
            'password.min'          => 'Le mot de passe doit faire au moins 8 caractères.',
            'password.confirmed'    => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'role'               => $validated['role'],
            'password'           => Hash::make($validated['password']),
            'email_verified_at'  => now(), // Admin crée = email vérifié
            'theme'              => 'light',
        ]);

        ActivityLog::log('created_user', $user, "Utilisateur \"{$user->name}\" créé par l'admin");

        return redirect()->route('team.index')
            ->with('success', "Compte de {$user->name} créé avec succès.");
    }

    // ── Formulaire édition ─────────────────────────────────
    public function edit(User $user): Response
    {
        return Inertia::render('Team/Form', [
            'member' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    // ── Mettre à jour ──────────────────────────────────────
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'role'     => 'required|in:admin,project_manager,staff_member',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $data = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'role'  => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        ActivityLog::log('updated_user', $user, "Profil de \"{$user->name}\" modifié");

        return redirect()->route('team.index')
            ->with('success', "Profil de {$user->name} mis à jour.");
    }

    // ── Changer le rôle rapidement ─────────────────────────
    public function updateRole(Request $request, User $user)
    {
        // Empêcher de modifier son propre rôle
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        $request->validate(['role' => 'required|in:admin,project_manager,staff_member']);

        $oldRole = $user->role;
        $user->update(['role' => $request->role]);

        ActivityLog::log('role_changed', $user, "Rôle changé : {$oldRole} → {$request->role}");

        return back()->with('success', "Rôle de {$user->name} mis à jour.");
    }

    // ── Supprimer ──────────────────────────────────────────
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::log('deleted_user', null, "Utilisateur \"{$name}\" supprimé");

        return redirect()->route('team.index')
            ->with('success', "Compte de {$name} supprimé.");
    }
}
