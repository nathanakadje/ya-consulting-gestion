<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ActivityLog, Client, Expense, Project, User};
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    //
    // ── INDEX — Liste des projets ──────────────────────────
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Project::with('client', 'lead')
            ->withSum('expenses', 'amount');

        // Chef de projet : voit uniquement ses projets
        if ($user->isChefProjet()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('project_lead_id', $user->id);
            });
        }

        // Filtres via query string
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                    ->orWhereHas(
                        'client',
                        fn($c) =>
                        $c->where('name', 'ilike', '%' . $request->search . '%')
                    );
            });
        }

        $projects = $query
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn($p) => [
                'id'                  => $p->id,
                'name'                => $p->name,
                'reference'           => $p->reference,
                'status'              => $p->status,
                'budget'              => $p->budget,
                'total_expenses'      => (float) ($p->expenses_sum_amount ?? 0),
                'margin_percent'      => $p->budget > 0
                    ? round((($p->budget - ($p->expenses_sum_amount ?? 0)) / $p->budget) * 100, 1)
                    : 0,
                'budget_used_percent' => $p->budget > 0
                    ? round((($p->expenses_sum_amount ?? 0) / $p->budget) * 100, 1)
                    : 0,
                'start_date'          => $p->start_date?->format('d/m/Y'),
                'end_date_planned'    => $p->end_date_planned?->format('d/m/Y'),
                'client'              => ['id' => $p->client?->id, 'name' => $p->client?->name],
                'lead'                => ['id' => $p->lead?->id,   'name' => $p->lead?->name],
            ]);

        // Stats pour les cartes en haut de page
        $stats = [
            'total'     => Project::count(),
            'en_cours'  => Project::where('status', 'en_cours')->count(),
            'termine'   => Project::where('status', 'termine')->count(),
            'en_pause'  => Project::where('status', 'en_pause')->count(),
            'total_budget' => Project::sum('budget'),
        ];

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'stats'    => $stats,
            'clients'  => Client::orderBy('name')->get(['id', 'name']),
            'filters'  => $request->only(['status', 'client_id', 'search']),
        ]);
    }

    // ── CREATE — Formulaire de création ───────────────────
    public function create(): Response
    {
        $this->authorizeRole(['admin', 'project_manager']);

        return Inertia::render('Projects/Form', [
            'project'  => null,
            'clients'  => Client::orderBy('name')->get(['id', 'name']),
            'users'    => User::whereIn('role', ['admin', 'project_manager'])
                ->orderBy('name')
                ->get(['id', 'name', 'role']),
        ]);
    }

    // ── STORE — Enregistrement ─────────────────────────────
    public function store(Request $request)
    {
        $this->authorizeRole(['admin', 'project_manager']);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'reference'          => 'nullable|string|max:50|unique:projects,reference',
            'description'        => 'nullable|string',
            'client_id'          => 'required|exists:clients,id',
            'project_lead_id'    => 'nullable|exists:users,id',
            'budget'             => 'required|numeric|min:0',
            'budget_main_oeuvre' => 'nullable|numeric|min:0',
            'budget_materiel'    => 'nullable|numeric|min:0',
            'budget_transport'   => 'nullable|numeric|min:0',
            'budget_autres'      => 'nullable|numeric|min:0',
            'start_date'         => 'required|date',
            'end_date_planned'   => 'required|date|after:start_date',
            'status'             => 'required|in:en_cours,termine,en_pause',
        ], [
            'name.required'            => 'Le nom du projet est obligatoire.',
            'client_id.required'       => 'Veuillez sélectionner un client.',
            'budget.required'          => 'Le budget est obligatoire.',
            'start_date.required'      => 'La date de début est obligatoire.',
            'end_date_planned.after'   => 'La date de fin doit être après la date de début.',
        ]);

        // Auto-générer une référence si vide
        if (empty($validated['reference'])) {
            $count = Project::count() + 1;
            $validated['reference'] = 'YA-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        }

        $project = Project::create(array_merge($validated, [
            'created_by' => $request->user()->id,
        ]));

        ActivityLog::log('created_project', $project, "Projet \"{$project->name}\" créé");

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "Projet \"{$project->name}\" créé avec succès.");
    }

    // ── SHOW — Détail d'un projet ──────────────────────────
    public function show(Project $project): Response
    {
        $project->load([
            'client',
            'lead',
            'creator',
            'expenses.category',
            'expenses.createdBy',
        ]);

        $totalExpenses = $project->expenses->sum('amount');
        $grossGain     = $project->budget - $totalExpenses;
        $marginPercent = $project->budget > 0
            ? round(($grossGain / $project->budget) * 100, 2)
            : 0;

        // Dépenses par catégorie (pour graphique)
        $byCategory = $project->expenses
            ->groupBy('category.name')
            ->map(fn($group) => [
                'name'   => $group->first()->category?->name ?? 'Divers',
                'color'  => $group->first()->category?->color ?? '#6b7280',
                'amount' => $group->sum('amount'),
            ])
            ->values();

        // Dépenses par mois (6 derniers mois, pour burn chart)
        $byMonth = $project->expenses
            ->groupBy(fn($e) => $e->expense_date->format('Y-m'))
            ->map(fn($g, $month) => [
                'month'  => \Carbon\Carbon::parse($month)->translatedFormat('M'),
                'amount' => $g->sum('amount'),
            ])
            ->sortKeys()
            ->values()
            ->take(6);

        return Inertia::render('Projects/Show', [
            'project' => [
                'id'                  => $project->id,
                'name'                => $project->name,
                'reference'           => $project->reference,
                'description'         => $project->description,
                'status'              => $project->status,
                'budget'              => $project->budget,
                'budget_main_oeuvre'  => $project->budget_main_oeuvre,
                'budget_materiel'     => $project->budget_materiel,
                'budget_transport'    => $project->budget_transport,
                'budget_autres'       => $project->budget_autres,
                'start_date'          => $project->start_date?->format('d/m/Y'),
                'end_date_planned'    => $project->end_date_planned?->format('d/m/Y'),
                'end_date_actual'     => $project->end_date_actual?->format('d/m/Y'),
                'client'              => $project->client,
                'lead'                => $project->lead ? ['id' => $project->lead->id, 'name' => $project->lead->name] : null,
                'creator'             => ['name' => $project->creator?->name],
            ],
            'financials' => [
                'total_expenses'      => $totalExpenses,
                'gross_gain'          => $grossGain,
                'margin_percent'      => $marginPercent,
                'budget_used_percent' => $project->budget > 0
                    ? round(($totalExpenses / $project->budget) * 100, 1)
                    : 0,
            ],
            'expenses'    => $project->expenses->map(fn($e) => [
                'id'           => $e->id,
                'description'  => $e->description,
                'amount'       => $e->amount,
                'expense_date' => $e->expense_date?->format('d/m/Y'),
                'status'       => $e->status,
                'category'     => ['name' => $e->category?->name, 'color' => $e->category?->color, 'icon' => $e->category?->icon],
                'created_by'   => ['name' => $e->createdBy?->name],
            ])->sortByDesc('id')->values(),
            'charts' => [
                'by_category' => $byCategory,
                'by_month'    => $byMonth,
            ],
        ]);
    }

    // ── EDIT — Formulaire de modification ─────────────────
    public function edit(Project $project): Response
    {
        $this->authorizeRole(['admin', 'project_manager']);

        return Inertia::render('Projects/Form', [
            'project' => $project->load('client'),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'users'   => User::whereIn('role', ['admin', 'project_manager'])
                ->orderBy('name')
                ->get(['id', 'name', 'role']),
        ]);
    }

    // ── UPDATE — Mise à jour ───────────────────────────────
    public function update(Request $request, Project $project)
    {
        $this->authorizeRole(['admin', 'project_manager']);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'reference'          => 'nullable|string|max:50|unique:projects,reference,' . $project->id,
            'description'        => 'nullable|string',
            'client_id'          => 'required|exists:clients,id',
            'project_lead_id'    => 'nullable|exists:users,id',
            'budget'             => 'required|numeric|min:0',
            'budget_main_oeuvre' => 'nullable|numeric|min:0',
            'budget_materiel'    => 'nullable|numeric|min:0',
            'budget_transport'   => 'nullable|numeric|min:0',
            'budget_autres'      => 'nullable|numeric|min:0',
            'start_date'         => 'required|date',
            'end_date_planned'   => 'required|date|after:start_date',
            'end_date_actual'    => 'nullable|date',
            'status'             => 'required|in:en_cours,termine,en_pause',
        ]);

        $old = $project->toArray();
        $project->update($validated);

        ActivityLog::log('updated_project', $project, "Projet \"{$project->name}\" modifié", $old, $project->fresh()->toArray());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "Projet mis à jour avec succès.");
    }

    // ── DESTROY — Suppression ─────────────────────────────
    public function destroy(Project $project)
    {
        $this->authorizeRole(['admin']);

        // Bloquer si dépenses validées
        if ($project->expenses()->where('status', 'validated')->exists()) {
            return back()->with('error', 'Impossible de supprimer un projet avec des dépenses validées.');
        }

        $name = $project->name;
        $project->delete();

        ActivityLog::log('deleted_project', null, "Projet \"{$name}\" supprimé");

        return redirect()
            ->route('projects.index')
            ->with('success', "Projet \"{$name}\" supprimé.");
    }

    // ── UPDATE STATUS — Changement de statut rapide ───────
    public function updateStatus(Request $request, Project $project)
    {
        $this->authorizeRole(['admin', 'project_manager']);

        $request->validate([
            'status' => 'required|in:en_cours,termine,en_pause',
        ]);

        $project->update([
            'status'          => $request->status,
            'end_date_actual' => $request->status === 'termine' ? now() : $project->end_date_actual,
        ]);

        ActivityLog::log('status_changed', $project, "Statut changé → {$request->status}");

        return back()->with('success', 'Statut mis à jour.');
    }

    // ── Helpers ───────────────────────────────────────────
    // private function authorizeRole(array $roles): void
    // {
    //     if (!in_array(auth()->user()->role, $roles)) {
    //         abort(403, 'Action non autorisée.');
    //     }
    // }
    private function authorizeRole(array $roles): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // On vérifie d'abord si l'utilisateur existe, puis son rôle
        if (!$user || !in_array($user->role, $roles)) {
            abort(403, 'Action non autorisée.');
        }
    }
}
