<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ActivityLog, Expense, ExpenseCategory, Project};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    //

    // ── INDEX global — toutes les dépenses ────────────────
    public function index(Request $request): Response
    {
        $user  = $request->user();
        $query = Expense::with(['project', 'category', 'createdBy'])
            ->latest('expense_date');

        // Chef de projet : uniquement ses projets
        if ($user->isChefProjet()) {
            $query->whereHas(
                'project',
                fn($q) =>
                $q->where('created_by', $user->id)
                    ->orWhere('project_lead_id', $user->id)
            );
        }

        // Filtres
        if ($request->filled('project_id'))  $query->where('project_id', $request->project_id);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('expense_date', $year)->whereMonth('expense_date', $month);
        }
        if ($request->filled('search')) {
            $query->where('description', 'ilike', '%' . $request->search . '%');
        }

        $expenses = $query->paginate(15)->withQueryString()->through(fn($e) => [
            'id'           => $e->id,
            'description'  => $e->description,
            'amount'       => $e->amount,
            'expense_date' => $e->expense_date?->format('d/m/Y'),
            'status'       => $e->status,
            'receipt_path' => $e->receipt_path,
            'notes'        => $e->notes,
            'project'      => ['id' => $e->project?->id, 'name' => $e->project?->name],
            'category'     => ['id' => $e->category?->id, 'name' => $e->category?->name, 'color' => $e->category?->color, 'icon' => $e->category?->icon],
            'created_by'   => ['name' => $e->createdBy?->name],
        ]);

        // Stats du mois courant
        $stats = [
            'total_this_month' => Expense::whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount'),
            'total_all'        => Expense::sum('amount'),
            'count_pending'    => Expense::where('status', 'pending')->count(),
            'count_this_month' => Expense::whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->count(),
        ];

        return Inertia::render('Expenses/Index', [
            'expenses'   => $expenses,
            'stats'      => $stats,
            'projects'   => Project::orderBy('name')->get(['id', 'name']),
            'categories' => ExpenseCategory::orderBy('name')->get(['id', 'name', 'color', 'icon']),
            'filters'    => $request->only(['project_id', 'category_id', 'status', 'month', 'search']),
        ]);
    }

    // ── STORE — Créer une dépense (depuis modal) ──────────
    public function store(Request $request)
    {
        $this->authorizeRole(['admin', 'project_manager']);

        $validated = $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'category_id'  => 'required|exists:expense_categories,id',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
            'status'       => 'sometimes|in:pending,validated,rejected',
            // Justificatif : optionnel, max 5 Mo, formats courants
            'receipt'      => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,webp',
        ], [
            'project_id.required'   => 'Veuillez sélectionner un projet.',
            'category_id.required'  => 'Veuillez sélectionner une catégorie.',
            'description.required'  => 'La description est obligatoire.',
            'amount.required'       => 'Le montant est obligatoire.',
            'amount.min'            => 'Le montant doit être supérieur à 0.',
            'expense_date.required' => 'La date est obligatoire.',
            'receipt.max'           => 'Le justificatif ne doit pas dépasser 5 Mo.',
            'receipt.mimes'         => 'Format accepté : PDF, JPG, PNG, WEBP.',
        ]);

        // Upload justificatif
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')
                ->store("receipts/project-{$validated['project_id']}", 'private');
        }

        $expense = Expense::create([
            ...$validated,
            'receipt_path' => $receiptPath,
            'status'       => $validated['status'] ?? 'validated',
            'created_by'   => $request->user()->id,
        ]);

        ActivityLog::log(
            'created_expense',
            $expense,
            "Dépense \"{$expense->description}\" ({$expense->amount} FCFA) ajoutée"
        );

        // Retour vers la page d'origine (projet ou liste globale)
        return back()->with('success', "Dépense ajoutée avec succès.");
    }

    // ── UPDATE — Modifier une dépense ─────────────────────
    public function update(Request $request, Expense $expense)
    {
        $this->authorizeRole(['admin', 'project_manager']);

        $validated = $request->validate([
            'category_id'  => 'required|exists:expense_categories,id',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
            'status'       => 'sometimes|in:pending,validated,rejected',
            'receipt'      => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,webp',
            'remove_receipt' => 'boolean',
        ]);

        // Gérer le justificatif
        $receiptPath = $expense->receipt_path;

        if ($request->boolean('remove_receipt') && $receiptPath) {
            Storage::disk('private')->delete($receiptPath);
            $receiptPath = null;
        }

        if ($request->hasFile('receipt')) {
            // Supprimer l'ancien si existant
            if ($receiptPath) Storage::disk('private')->delete($receiptPath);
            $receiptPath = $request->file('receipt')
                ->store("receipts/project-{$expense->project_id}", 'private');
        }

        $old = $expense->toArray();
        $expense->update([
            ...$validated,
            'receipt_path' => $receiptPath,
        ]);

        ActivityLog::log('updated_expense', $expense, "Dépense modifiée", $old, $expense->fresh()->toArray());

        return back()->with('success', "Dépense mise à jour.");
    }

    // ── DESTROY — Supprimer une dépense ───────────────────
    public function destroy(Expense $expense)
    {
        $this->authorizeRole(['admin', 'project_manager']);

        // Supprimer le fichier justificatif si présent
        if ($expense->receipt_path) {
            Storage::disk('private')->delete($expense->receipt_path);
        }

        $desc = $expense->description;
        $expense->delete();

        ActivityLog::log('deleted_expense', null, "Dépense \"{$desc}\" supprimée");

        return back()->with('success', "Dépense supprimée.");
    }

    // ── DOWNLOAD — Télécharger le justificatif ───────────
    public function downloadReceipt(Expense $expense)
    {
        if (!$expense->receipt_path || !Storage::disk('private')->exists($expense->receipt_path)) {
            abort(404, 'Justificatif introuvable.');
        }

        return Storage::disk('private')->download($expense->receipt_path);
    }

    // ── Helper ────────────────────────────────────────────
    private function authorizeRole(array $roles): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, $roles)) {
            abort(403, 'Action non autorisée.');
        }
    }
}
