<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Expense, ExpenseCategory, Project};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    //
    // ── INDEX — Page rapports ──────────────────────────────
    public function index(Request $request): Response
    {
        $this->authorizeRole(['admin', 'project_manager']);

        $month     = $request->get('month', now()->format('Y-m'));
        $projectId = $request->get('project_id');

        [$year, $monthNum] = explode('-', $month);

        // ── Stats du mois sélectionné ──────────────────────
        $monthlyExpenses = Expense::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $monthNum)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->sum('amount');

        // Mois précédent
        $prevDate    = Carbon::createFromDate($year, $monthNum, 1)->subMonth();
        $prevExpenses = Expense::whereYear('expense_date', $prevDate->year)
            ->whereMonth('expense_date', $prevDate->month)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->sum('amount');

        // Projets terminés ce mois
        $terminatedThisMonth = Project::whereYear('end_date_actual', $year)
            ->whereMonth('end_date_actual', $monthNum)
            ->when($projectId, fn($q) => $q->where('id', $projectId))
            ->withSum('expenses', 'amount')
            ->get();

        $monthlyGains = $terminatedThisMonth->sum(
            fn($p) =>
            max($p->budget - ($p->expenses_sum_amount ?? 0), 0)
        );

        // Dépenses par catégorie ce mois
        $byCategory = Expense::select('category_id', DB::raw('SUM(amount) as total'))
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $monthNum)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->with('category')
            ->groupBy('category_id')
            ->get()
            ->map(fn($row) => [
                'name'   => $row->category?->name ?? 'Divers',
                'color'  => $row->category?->color ?? '#6b7280',
                'icon'   => $row->category?->icon ?? 'category',
                'amount' => (float) $row->total,
            ])
            ->sortByDesc('amount')
            ->values();

        // Évolution sur 6 mois (pour le graphique)
        $evolution = collect(range(5, 0))->map(function ($offset) use ($year, $monthNum, $projectId) {
            $date = Carbon::createFromDate($year, $monthNum, 1)->subMonths($offset);
            $total = Expense::whereYear('expense_date', $date->year)
                ->whereMonth('expense_date', $date->month)
                ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                ->sum('amount');
            return [
                'month'  => $date->translatedFormat('M y'),
                'amount' => (float) $total,
            ];
        });

        // Transactions du mois (preview)
        $transactions = Expense::with(['project', 'category', 'createdBy'])
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $monthNum)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->latest('expense_date')
            ->take(20)
            ->get()
            ->map(fn($e) => [
                'id'           => $e->id,
                'date'         => $e->expense_date?->format('d/m/Y'),
                'description'  => $e->description,
                'amount'       => $e->amount,
                'status'       => $e->status,
                'project'      => ['name' => $e->project?->name],
                'category'     => ['name' => $e->category?->name, 'color' => $e->category?->color],
                'created_by'   => ['name' => $e->createdBy?->name],
            ]);

        // Projets actifs ce mois
        $activeProjects = Project::where('status', 'en_cours')
            ->when($projectId, fn($q) => $q->where('id', $projectId))
            ->withSum('expenses', 'amount')
            ->count();

        $stats = [
            'monthly_expenses'    => $monthlyExpenses,
            'monthly_gains'       => $monthlyGains,
            'monthly_result'      => $monthlyGains - $monthlyExpenses,
            'prev_expenses'       => $prevExpenses,
            'evolution_pct'       => $prevExpenses > 0
                ? round((($monthlyExpenses - $prevExpenses) / $prevExpenses) * 100, 1)
                : 0,
            'active_projects'     => $activeProjects,
            'terminated_count'    => $terminatedThisMonth->count(),
            'total_transactions'  => Expense::whereYear('expense_date', $year)
                ->whereMonth('expense_date', $monthNum)->count(),
        ];

        return Inertia::render('Reports/Index', [
            'stats'        => $stats,
            'byCategory'   => $byCategory,
            'evolution'    => $evolution,
            'transactions' => $transactions,
            'projects'     => Project::orderBy('name')->get(['id', 'name', 'status']),
            'filters'      => compact('month', 'projectId'),
            'selectedMonth' => Carbon::createFromDate($year, $monthNum, 1)
                ->translatedFormat('F Y'),
        ]);
    }

    // ── EXPORT PDF ─────────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $this->authorizeRole(['admin', 'project_manager']);

        $request->validate([
            'month'      => 'required|date_format:Y-m',
            'project_id' => 'nullable|exists:projects,id',
            'type'       => 'required|in:monthly,project,audit',
        ]);

        [$year, $monthNum] = explode('-', $request->month);
        $projectId = $request->project_id;

        // Données pour le PDF
        $data = $this->buildReportData($year, $monthNum, $projectId, $request->type);

        // Générer le PDF avec DomPDF (barryvdh/laravel-dompdf)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = "rapport-ya-consulting-{$request->month}.pdf";

        return $pdf->download($filename);
    }

    // ── EXPORT EXCEL ───────────────────────────────────────
    public function exportExcel(Request $request)
    {
        $this->authorizeRole(['admin', 'project_manager']);

        $request->validate([
            'month'      => 'required|date_format:Y-m',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        [$year, $monthNum] = explode('-', $request->month);

        // Export avec Maatwebsite Excel
        // composer require maatwebsite/excel
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MonthlyReportExport($year, $monthNum, $request->project_id),
            "rapport-ya-consulting-{$request->month}.xlsx"
        );
    }

    // ── Données communes rapport ───────────────────────────
    private function buildReportData(string $year, string $month, ?int $projectId, string $type): array
    {
        $expenses = Expense::with(['project', 'category', 'createdBy'])
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->orderBy('expense_date')
            ->get();

        $projects = Project::when($projectId, fn($q) => $q->where('id', $projectId))
            ->withSum('expenses', 'amount')
            ->get();

        return [
            'title'       => "Rapport mensuel — " . Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'),
            'generated_at' => now()->format('d/m/Y H:i'),
            'period'      => Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'),
            'expenses'    => $expenses,
            'projects'    => $projects,
            'total'       => $expenses->sum('amount'),
            'type'        => $type,
            'company'     => config('app.name', 'Ya Consulting'),
        ];
    }

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
