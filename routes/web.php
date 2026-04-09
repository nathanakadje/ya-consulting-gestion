<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});
// ── Page d'accueil → rediriger vers login ─────────────────────
// Route::get('/', fn() => redirect()->route('login'));
Route::get('/dashboard', [DashboardController::class, 'Index'])
    ->name('dashboard');
// ══════════════════════════════════════════════════════════════
//  Routes protégées (utilisateur connecté + email vérifié)
// ══════════════════════════════════════════════════════════════
Route::middleware(['auth', 'verified'])->group(function () {

    // ── Dashboard ────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ── Projets ───────────────────────────────────────────────
    // GET    /projects          → index  (liste)
    // GET    /projects/create   → create (formulaire création)
    // POST   /projects          → store  (enregistrer)
    // GET    /projects/{id}     → show   (détail)
    // GET    /projects/{id}/edit → edit  (formulaire édition)
    // PUT    /projects/{id}     → update (mettre à jour)
    // DELETE /projects/{id}     → destroy (supprimer)
    // Route::resource('projects', ProjectController::class);

    // Changement de statut rapide (PATCH depuis le détail)
    // Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])
    //      ->name('projects.status');

    // ── Thème utilisateur ─────────────────────────────────────
    // PATCH /user/theme → appelé par le toggle dans AppLayout.vue
    Route::patch('user/theme', [UserController::class, 'updateTheme'])
        ->name('user.theme');

    // ── Routes Semaine 3 (à décommenter) ─────────────────────
    // Route::resource('projects.expenses', ExpenseController::class)->shallow();

    // ── Routes Semaine 4 (à décommenter) ─────────────────────
    // Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    // Route::post('reports/export', [ReportController::class, 'export'])->name('reports.export');
});
