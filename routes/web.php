<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamController;
use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Index');
    })->name('dashboard');

    Route::patch('user/theme', [UserController::class, 'updateTheme'])
        ->name('user.theme');

    Route::resource('projects', ProjectController::class);


    // ── Dépenses ──────────────────────────────────────────────
    // GET    /expenses               → index  (liste globale)
    // POST   /expenses               → store  (créer)
    // PUT    /expenses/{expense}     → update (modifier)
    // DELETE /expenses/{expense}     → destroy (supprimer)

    // ── Rapports ──────────────────────────────────────────
    Route::get('/reports',              [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pdf',   [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.excel');

    // ── Gestion équipe/utilisateurs : admin seulement ────
    Route::middleware('role:admin')->group(function () {
        Route::get('/team', [TeamController::class, 'index'])
            ->middleware('role:admin');
        // Route::get('/team',              [TeamController::class, 'index'])->name('team.index');
        Route::get('/team/create',       [TeamController::class, 'create'])->name('team.create');
        Route::post('/team',             [TeamController::class, 'store'])->name('team.store');
        Route::get('/team/{user}/edit',  [TeamController::class, 'edit'])->name('team.edit');
        Route::put('/team/{user}',       [TeamController::class, 'update'])->name('team.update');
        Route::delete('/team/{user}',    [TeamController::class, 'destroy'])->name('team.destroy');
        Route::patch('/team/{user}/role', [TeamController::class, 'updateRole'])->name('team.role');
    });

    // ── Rapports : admin + chef_projet ───────────────────
    Route::middleware('role:admin,project_manager')->group(function () {
        Route::get('/reports',              [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-pdf',   [ReportController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
    });

    Route::get('/expenses',                     [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/{expense}/receipt',   [ExpenseController::class, 'downloadReceipt'])->name('expenses.receipt');
    // Écriture : admin + chef_projet
    Route::middleware('role:admin,project_manager')->group(function () {
        Route::post('/expenses',             [ExpenseController::class, 'store'])->name('expenses.store');
        Route::put('/expenses/{expense}',    [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        // Route::post('/expenses',                    [ExpenseController::class, 'store'])->name('expenses.store');
        // Route::put('/expenses/{expense}',           [ExpenseController::class, 'update'])->name('expenses.update');
        // Route::delete('/expenses/{expense}',        [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        // Téléchargement justificatif
    });
});
