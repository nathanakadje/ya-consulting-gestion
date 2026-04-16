<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Expense, Project};
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        // ── Base query filtrée selon le rôle ──────────────────
        // Admin        → tous les projets
        // Chef projet  → seulement ses projets (créés ou dont il est lead)
        // Collaborateur → tous (lecture seule)
        $projectQuery = Project::query();


        if ($user->isChefProjet()) {
            $projectQuery->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('project_lead_id', $user->id);
            });
        }

        // ── Stats générales ────────────────────────────────────
        $activeProjects  = (clone $projectQuery)->where('status', 'en_cours')->count();
        $pausedProjects  = (clone $projectQuery)->where('status', 'en_pause')->count();
        $terminedProjects = (clone $projectQuery)->where('status', 'termine')->count();
        $totalProjects   = (clone $projectQuery)->count();
        $totalBudget     = (clone $projectQuery)->sum('budget');

        // Dépenses du mois — filtrées sur le périmètre du user
        $expenseQuery = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);

        if ($user->isChefProjet()) {
            // Restreindre aux projets du chef
            $userProjectIds = (clone $projectQuery)->pluck('id');
            $expenseQuery->whereIn('project_id', $userProjectIds);
        }

        $expensesThisMonth = (float) $expenseQuery->sum('amount');

        // ── Marge moyenne (projets terminés) ──────────────────
        $avgMargin = $this->calcAvgMargin(clone $projectQuery);

        // ── Projets récents (5 derniers modifiés) ─────────────
        $recentProjects = (clone $projectQuery)
            ->with('client:id,name')
            ->withSum('expenses', 'amount')
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(function ($p) {
                $totalExpenses    = (float) ($p->expenses_sum_amount ?? 0);
                $budgetUsedPct    = $p->budget > 0
                    ? round(($totalExpenses / $p->budget) * 100, 1)
                    : 0;

                return [
                    'id'                  => $p->id,
                    'name'                => $p->name,
                    'reference'           => $p->reference,
                    'status'              => $p->status,
                    'budget'              => (float) $p->budget,
                    'total_expenses'      => $totalExpenses,
                    'budget_used_percent' => $budgetUsedPct,
                    'client'              => [
                        'id'   => $p->client?->id,
                        'name' => $p->client?->name ?? '—',
                    ],
                ];
            });

        // ── Dépenses récentes (6 dernières) ───────────────────
        $recentExpensesQuery = Expense::with([
            'category:id,name,color,icon',
            'project:id,name',
        ])->latest('expense_date');

        // Chef de projet : uniquement ses projets
        if ($user->isChefProjet()) {
            $userProjectIds = $userProjectIds ?? (clone $projectQuery)->pluck('id');
            $recentExpensesQuery->whereIn('project_id', $userProjectIds);
        }

        $recentExpenses = $recentExpensesQuery
            ->take(6)
            ->get()
            ->map(fn($e) => [
                'id'          => $e->id,
                'description' => $e->description,
                'amount'      => (float) $e->amount,
                'expense_date' => $e->expense_date?->format('d/m/Y'),
                'category'    => [
                    'id'    => $e->category?->id,
                    'name'  => $e->category?->name  ?? 'Divers',
                    'color' => $e->category?->color  ?? '#6b7280',
                    'icon'  => $e->category?->icon   ?? 'receipt',
                ],
                'project'     => [
                    'id'   => $e->project?->id,
                    'name' => $e->project?->name ?? '—',
                ],
            ]);

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'total_projects'      => $totalProjects,
                'active_projects'     => $activeProjects,
                'paused_projects'     => $pausedProjects,
                'terminated_projects' => $terminedProjects,
                'total_budget'        => (float) $totalBudget,
                'avg_margin'          => $avgMargin,
                'expenses_this_month' => $expensesThisMonth,
            ],
            'recentProjects' => $recentProjects,
            'recentExpenses' => $recentExpenses,
        ]);
    }

    // ── Marge moyenne sur projets terminés ────────────────────
    private function calcAvgMargin($query): float
    {
        $projects = (clone $query)
            ->where('status', 'termine')
            ->where('budget', '>', 0)        // exclure les budgets à 0
            ->withSum('expenses', 'amount')
            ->get();

        if ($projects->isEmpty()) return 0.0;

        $totalMargin = $projects->sum(function ($p) {
            $spent  = (float) ($p->expenses_sum_amount ?? 0);
            $budget = (float) $p->budget;
            return (($budget - $spent) / $budget) * 100;
        });

        return round($totalMargin / $projects->count(), 1);
    }
}
