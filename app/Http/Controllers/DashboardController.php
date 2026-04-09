<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Expense, Project};
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    //
    public function index(Request $request): Response
    {
        $user = $request->user();

        // ── Statistiques globales ──────────────────────────────

        // Pour un chef de projet, n'afficher que ses projets
        $projectQuery = Project::query();
        if ($user->isChefProjet()) {
            $projectQuery->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('project_lead_id', $user->id);
            });
        }

        $stats = [
            'active_projects'    => (clone $projectQuery)->where('status', 'en_cours')->count(),
            'paused_projects'    => (clone $projectQuery)->where('status', 'en_pause')->count(),
            'total_budget'       => (clone $projectQuery)->sum('budget'),
            'avg_margin'         => $this->calcAvgMargin(clone $projectQuery),
            'expenses_this_month' => Expense::whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount'),
        ];

        // ── Projets récents ──────────────────────────────────
        $recentProjects = (clone $projectQuery)
            ->with('client')
            ->withSum('expenses', 'amount')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'id'                 => $p->id,
                'name'               => $p->name,
                'status'             => $p->status,
                'budget'             => $p->budget,
                'budget_used_percent' => $p->budget > 0
                    ? round(($p->expenses_sum_amount / $p->budget) * 100, 1)
                    : 0,
                'client'             => ['name' => $p->client?->name],
            ]);

        // ── Dépenses récentes ────────────────────────────────
        $recentExpenses = Expense::with(['category', 'project'])
            ->latest('expense_date')
            ->take(6)
            ->get()
            ->map(fn($e) => [
                'id'          => $e->id,
                'description' => $e->description,
                'amount'      => $e->amount,
                'category'    => [
                    'name'  => $e->category?->name,
                    'color' => $e->category?->color,
                    'icon'  => $e->category?->icon,
                ],
                'project'     => ['name' => $e->project?->name],
            ]);

        return Inertia::render('Dashboard/Index', [
            'stats'          => $stats,
            'recentProjects' => $recentProjects,
            'recentExpenses' => $recentExpenses,
        ]);
    }

    /**
     * Calcule la marge moyenne sur les projets terminés
     */
    private function calcAvgMargin($query): float
    {
        $projects = (clone $query)
            ->where('status', 'termine')
            ->withSum('expenses', 'amount')
            ->get();

        if ($projects->isEmpty()) return 0;

        $totalMargin = $projects->sum(
            fn($p) =>
            $p->budget > 0
                ? (($p->budget - ($p->expenses_sum_amount ?? 0)) / $p->budget) * 100
                : 0
        );

        return round($totalMargin / $projects->count(), 1);
    }
}
