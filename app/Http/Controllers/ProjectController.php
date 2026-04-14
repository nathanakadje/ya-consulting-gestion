<?php

namespace App\Http\Controllers;

use App\Models\{ActivityLog, Client, Expense, ExpenseCategory, Project, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    // ── INDEX ──────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $user  = $request->user();
        $query = Project::with('client', 'lead')->withSum('expenses', 'amount');

        // Chef de projet : ses projets uniquement
        if ($user->isChefProjet()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('project_lead_id', $user->id);
            });
        }

        // Filtres
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhereHas('client', fn($c) => $c->where('name', 'ilike', "%{$search}%"));
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
                'budget'              => (float) $p->budget,
                'total_expenses'      => (float) ($p->expenses_sum_amount ?? 0),
                'margin_percent'      => $p->budget > 0
                    ? round((($p->budget - ($p->expenses_sum_amount ?? 0)) / $p->budget) * 100, 1)
                    : 0,
                'budget_used_percent' => $p->budget > 0
                    ? round((($p->expenses_sum_amount ?? 0) / $p->budget) * 100, 1)
                    : 0,
                'start_date'       => $p->start_date?->format('d/m/Y'),
                'end_date_planned' => $p->end_date_planned?->format('d/m/Y'),
                'client'           => ['id' => $p->client?->id, 'name' => $p->client?->name],
                'lead'             => ['id' => $p->lead?->id,   'name' => $p->lead?->name],
            ]);

        // Stats pour les cartes
        $baseQuery = $user->isChefProjet()
            ? Project::where(fn($q) => $q->where('created_by', $user->id)->orWhere('project_lead_id', $user->id))
            : Project::query();

        $stats = [
            'total'        => (clone $baseQuery)->count(),
            'en_cours'     => (clone $baseQuery)->where('status', 'en_cours')->count(),
            'termine'      => (clone $baseQuery)->where('status', 'termine')->count(),
            'en_pause'     => (clone $baseQuery)->where('status', 'en_pause')->count(),
            'total_budget' => (clone $baseQuery)->sum('budget'),
        ];

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'stats'    => $stats,
            'clients'  => Client::orderBy('name')->get(['id', 'name']),
            'filters'  => $request->only(['status', 'client_id', 'search']),
        ]);
    }

    // ── CREATE ─────────────────────────────────────────────
    public function create(): Response
    {
        $this->authorizeRole(['admin', 'project_manager']); // ← CORRIGÉ

        return Inertia::render('Projects/Form', [
            'project' => null,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'users'   => User::whereIn('role', ['admin', 'project_manager']) // ← CORRIGÉ
                ->orderBy('name')
                ->get(['id', 'name', 'role']),
        ]);
    }

    // ── STORE ──────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorizeRole(['admin', 'project_manager']); // ← CORRIGÉ

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
            'name.required'          => 'Le nom du projet est obligatoire.',
            'client_id.required'     => 'Veuillez sélectionner un client.',
            'budget.required'        => 'Le budget est obligatoire.',
            'start_date.required'    => 'La date de début est obligatoire.',
            'end_date_planned.after' => 'La date de fin doit être postérieure à la date de début.',
        ]);

        // Auto-référence
        if (empty($validated['reference'])) {
            $count = Project::withTrashed()->count() + 1;
            $validated['reference'] = 'YA-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        }

        // Valeurs nullables → 0 par défaut
        foreach (['budget_main_oeuvre', 'budget_materiel', 'budget_transport', 'budget_autres'] as $field) {
            $validated[$field] = $validated[$field] ?? 0;
        }

        $project = Project::create(array_merge($validated, [
            'created_by' => $request->user()->id,
        ]));

        ActivityLog::log('created_project', $project, "Projet \"{$project->name}\" créé");

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "Projet \"{$project->name}\" créé avec succès ✓");
    }

    // ── SHOW ───────────────────────────────────────────────
    public function show(Project $project): Response
    {
        $project->load(['client', 'lead', 'creator', 'expenses.category', 'expenses.createdBy']);

        $totalExpenses = (float) $project->expenses->sum('amount');
        $grossGain     = (float) $project->budget - $totalExpenses;
        $marginPercent = $project->budget > 0
            ? round(($grossGain / $project->budget) * 100, 2)
            : 0;

        // Dépenses par catégorie
        $byCategory = $project->expenses
            ->groupBy(fn($e) => $e->category?->name ?? 'Divers')
            ->map(fn($group) => [
                'name'   => $group->first()->category?->name ?? 'Divers',
                'color'  => $group->first()->category?->color ?? '#6b7280',
                'icon'   => $group->first()->category?->icon  ?? 'category',
                'amount' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('amount')
            ->values();

        // Dépenses par mois (6 derniers)
        $byMonth = $project->expenses
            ->groupBy(fn($e) => $e->expense_date->format('Y-m'))
            ->map(fn($g, $month) => [
                'month'  => \Carbon\Carbon::parse($month)->translatedFormat('M y'),
                'amount' => (float) $g->sum('amount'),
            ])
            ->sortKeys()
            ->values()
            ->take(-6);

        return Inertia::render('Projects/Show', [
            'project' => [
                'id'                 => $project->id,
                'name'               => $project->name,
                'reference'          => $project->reference,
                'description'        => $project->description,
                'status'             => $project->status,
                'budget'             => (float) $project->budget,
                'budget_main_oeuvre' => (float) $project->budget_main_oeuvre,
                'budget_materiel'    => (float) $project->budget_materiel,
                'budget_transport'   => (float) $project->budget_transport,
                'budget_autres'      => (float) $project->budget_autres,
                // Format lisible pour affichage
                'start_date'         => $project->start_date?->format('d/m/Y'),
                'end_date_planned'   => $project->end_date_planned?->format('d/m/Y'),
                'end_date_actual'    => $project->end_date_actual?->format('d/m/Y'),
                'client'             => $project->client ? [
                    'id'   => $project->client->id,
                    'name' => $project->client->name,
                ] : null,
                'lead'    => $project->lead ? [
                    'id'   => $project->lead->id,
                    'name' => $project->lead->name,
                ] : null,
                'creator' => ['name' => $project->creator?->name],
            ],
            'financials' => [
                'total_expenses'      => $totalExpenses,
                'gross_gain'          => $grossGain,
                'margin_percent'      => $marginPercent,
                'budget_used_percent' => $project->budget > 0
                    ? round(($totalExpenses / $project->budget) * 100, 1)
                    : 0,
            ],
            'expenses' => $project->expenses
                ->sortByDesc('id')
                ->values()
                ->map(fn($e) => [
                    'id'               => $e->id,
                    'description'      => $e->description,
                    'amount'           => (float) $e->amount,
                    'expense_date'     => $e->expense_date?->format('d/m/Y'),
                    'expense_date_raw' => $e->expense_date?->format('Y-m-d'), // pour le modal édition
                    'status'           => $e->status,
                    'receipt_path'     => $e->receipt_path,
                    'notes'            => $e->notes,
                    'category'         => [
                        'id'    => $e->category?->id,
                        'name'  => $e->category?->name,
                        'color' => $e->category?->color,
                        'icon'  => $e->category?->icon,
                    ],
                    'created_by' => ['name' => $e->createdBy?->name],
                ]),
            'charts' => [
                'by_category' => $byCategory,
                'by_month'    => $byMonth,
            ],
            // ← AJOUT : nécessaire pour le modal d'ajout de dépense
            'categories' => ExpenseCategory::orderBy('name')->get(['id', 'name', 'color', 'icon']),
        ]);
    }

    // ── EDIT ───────────────────────────────────────────────
    public function edit(Project $project): Response
    {
        $this->authorizeRole(['admin', 'project_manager']); // ← CORRIGÉ

        return Inertia::render('Projects/Form', [
            // On passe le projet avec les dates au format Y-m-d pour les inputs date
            'project' => array_merge($project->load('client')->toArray(), [
                'start_date_raw'          => $project->start_date?->format('Y-m-d'),
                'end_date_planned_raw'    => $project->end_date_planned?->format('Y-m-d'),
                'end_date_actual_raw'     => $project->end_date_actual?->format('Y-m-d'),
                // client_id en entier pour le select
                'client_id'               => $project->client_id,
                'project_lead_id'         => $project->project_lead_id,
            ]),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'users'   => User::whereIn('role', ['admin', 'project_manager']) // ← CORRIGÉ
                ->orderBy('name')
                ->get(['id', 'name', 'role']),
        ]);
    }

    // ── UPDATE ─────────────────────────────────────────────
    public function update(Request $request, Project $project)
    {
        $this->authorizeRole(['admin', 'project_manager']); // ← CORRIGÉ

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
            'end_date_planned'   => 'required|date|after_or_equal:start_date',
            'end_date_actual'    => 'nullable|date',
            'status'             => 'required|in:en_cours,termine,en_pause',
        ]);

        foreach (['budget_main_oeuvre', 'budget_materiel', 'budget_transport', 'budget_autres'] as $f) {
            $validated[$f] = $validated[$f] ?? 0;
        }

        $old = $project->toArray();
        $project->update($validated);

        ActivityLog::log(
            'updated_project',
            $project,
            "Projet \"{$project->name}\" modifié",
            $old,
            $project->fresh()->toArray()
        );

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "Projet mis à jour avec succès ✓");
    }

    // ── DESTROY ────────────────────────────────────────────
    public function destroy(Project $project)
    {
        $this->authorizeRole(['admin']);

        if ($project->expenses()->where('status', 'validated')->exists()) {
            return back()->with('error', 'Impossible : le projet a des dépenses validées.');
        }

        $name = $project->name;
        $project->delete();

        ActivityLog::log('deleted_project', null, "Projet \"{$name}\" supprimé");

        return redirect()
            ->route('projects.index')
            ->with('success', "Projet \"{$name}\" supprimé.");
    }

    // ── UPDATE STATUS ──────────────────────────────────────
    public function updateStatus(Request $request, Project $project)
    {
        $this->authorizeRole(['admin', 'project_manager']); // ← CORRIGÉ

        $request->validate(['status' => 'required|in:en_cours,termine,en_pause']);

        $project->update([
            'status'          => $request->status,
            'end_date_actual' => $request->status === 'termine'
                ? ($project->end_date_actual ?? now())
                : $project->end_date_actual,
        ]);

        ActivityLog::log('status_changed', $project, "Statut → {$request->status}");

        return back()->with('success', 'Statut mis à jour.');
    }

    // ── Helper ─────────────────────────────────────────────
    private function authorizeRole(array $roles): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !in_array($user->role, $roles)) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
