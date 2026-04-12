<!DOCTYPE html>
{{-- resources/views/reports/pdf.blade.php
     Template Blade rendu par DomPDF (barryvdh/laravel-dompdf)
     Police : DejaVu Sans (incluse dans DomPDF, supporte les caractères spéciaux)
--}}
<html lang="fr">
<head>
<meta charset="UTF-8" />
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9pt;
        color: #2a3439;
        background: #fff;
    }

    /* ── En-tête du document ──────────────────────────── */
    .doc-header {
        background: #0053db;
        color: #fff;
        padding: 20px 28px 16px;
        margin-bottom: 20px;
    }
    .doc-header .company {
        font-size: 18pt;
        font-weight: bold;
        letter-spacing: -0.5px;
    }
    .doc-header .subtitle {
        font-size: 9pt;
        opacity: 0.75;
        margin-top: 2px;
    }
    .doc-header .meta {
        margin-top: 10px;
        font-size: 8pt;
        opacity: 0.8;
    }

    /* ── Titre rapport ────────────────────────────────── */
    .report-title {
        padding: 0 28px 14px;
        border-bottom: 2px solid #dbe1ff;
        margin-bottom: 18px;
    }
    .report-title h1 {
        font-size: 13pt;
        font-weight: bold;
        color: #0053db;
    }
    .report-title p {
        font-size: 8pt;
        color: #566166;
        margin-top: 3px;
    }

    /* ── Cartes résumé ────────────────────────────────── */
    .summary-grid {
        display: table;
        width: calc(100% - 56px);
        margin: 0 28px 20px;
        border-collapse: separate;
        border-spacing: 8px 0;
    }
    .summary-card {
        display: table-cell;
        width: 25%;
        background: #f0f4f7;
        border-radius: 8px;
        padding: 10px 12px;
        vertical-align: top;
        border-left: 3px solid #0053db;
    }
    .summary-card.green  { border-left-color: #10b981; }
    .summary-card.violet { border-left-color: #8b5cf6; }
    .summary-card.amber  { border-left-color: #f59e0b; }
    .summary-card .label {
        font-size: 7pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #717c82;
        margin-bottom: 4px;
    }
    .summary-card .value {
        font-size: 11pt;
        font-weight: bold;
        color: #0f1923;
    }

    /* ── Section titre ────────────────────────────────── */
    .section-title {
        font-size: 9pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #0053db;
        padding: 0 28px 6px;
        margin-bottom: 8px;
        border-bottom: 1px solid #dbe1ff;
    }

    /* ── Tableau transactions ─────────────────────────── */
    .table-wrap { padding: 0 28px; margin-bottom: 20px; }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }
    thead tr {
        background: #0053db;
        color: #fff;
    }
    thead th {
        padding: 7px 8px;
        text-align: left;
        font-weight: bold;
        font-size: 7.5pt;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    thead th.right { text-align: right; }

    tbody tr:nth-child(even) { background: #f7f9fb; }
    tbody tr:nth-child(odd)  { background: #fff; }
    tbody td {
        padding: 6px 8px;
        color: #2a3439;
        border-bottom: 1px solid #e8eff3;
    }
    tbody td.right  { text-align: right; font-weight: bold; }
    tbody td.muted  { color: #717c82; }
    tbody td.mono   { font-family: 'DejaVu Sans Mono', monospace; font-size: 7.5pt; }

    /* Badge statut */
    .badge {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 10px;
        font-size: 7pt;
        font-weight: bold;
        text-transform: uppercase;
    }
    .badge-validated { background: #d1fae5; color: #065f46; }
    .badge-pending   { background: #fef3c7; color: #92400e; }
    .badge-rejected  { background: #fee2e2; color: #991b1b; }

    /* ── Répartition catégories ───────────────────────── */
    .categories-wrap { padding: 0 28px; margin-bottom: 20px; }
    .cat-row {
        display: table;
        width: 100%;
        margin-bottom: 7px;
    }
    .cat-label {
        display: table-cell;
        width: 140px;
        font-size: 8pt;
        vertical-align: middle;
        padding-right: 10px;
    }
    .cat-bar-wrap {
        display: table-cell;
        vertical-align: middle;
        padding-right: 10px;
    }
    .cat-bar-bg {
        background: #e8eff3;
        border-radius: 3px;
        height: 8px;
        width: 100%;
    }
    .cat-bar-fill {
        height: 8px;
        border-radius: 3px;
    }
    .cat-amount {
        display: table-cell;
        width: 90px;
        text-align: right;
        font-size: 8pt;
        font-weight: bold;
        vertical-align: middle;
    }

    /* ── Pied de page ─────────────────────────────────── */
    .doc-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 8px 28px;
        background: #f0f4f7;
        border-top: 1px solid #d9e4ea;
        font-size: 7pt;
        color: #717c82;
        display: table;
        width: 100%;
    }
    .doc-footer .left  { display: table-cell; text-align: left; }
    .doc-footer .right { display: table-cell; text-align: right; }
</style>
</head>
<body>

{{-- En-tête --}}
<div class="doc-header">
    <div class="company">{{ $company }}</div>
    <div class="subtitle">Système de gestion de projets</div>
    <div class="meta">Généré le {{ $generated_at }} · {{ $title }}</div>
</div>

{{-- Titre --}}
<div class="report-title">
    <h1>{{ $title }}</h1>
    <p>Rapport financier — Période : {{ $period }}</p>
</div>

{{-- Résumé chiffres clés --}}
<div class="summary-grid">
    <div class="summary-card">
        <div class="label">Total dépenses</div>
        <div class="value">{{ number_format($total, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="summary-card green">
        <div class="label">Projets inclus</div>
        <div class="value">{{ $projects->count() }}</div>
    </div>
    <div class="summary-card violet">
        <div class="label">Transactions</div>
        <div class="value">{{ $expenses->count() }}</div>
    </div>
    <div class="summary-card amber">
        <div class="label">Moy. / transaction</div>
        <div class="value">
            @if($expenses->count() > 0)
                {{ number_format($total / $expenses->count(), 0, ',', ' ') }} FCFA
            @else
                —
            @endif
        </div>
    </div>
</div>

{{-- Répartition par catégorie --}}
@if($expenses->groupBy('category.name')->count() > 0)
<p class="section-title">Répartition par catégorie</p>
<div class="categories-wrap">
    @php
        $byCategory = $expenses->groupBy(fn($e) => $e->category?->name ?? 'Divers')
            ->map(fn($group) => [
                'name'   => $group->first()->category?->name ?? 'Divers',
                'color'  => $group->first()->category?->color ?? '#6b7280',
                'amount' => $group->sum('amount'),
            ])
            ->sortByDesc('amount');
        $maxCat = $byCategory->max('amount');
    @endphp
    @foreach($byCategory as $cat)
    <div class="cat-row">
        <div class="cat-label">{{ $cat['name'] }}</div>
        <div class="cat-bar-wrap">
            <div class="cat-bar-bg">
                <div class="cat-bar-fill"
                     style="width: {{ $maxCat > 0 ? round($cat['amount'] / $maxCat * 100) : 0 }}%;
                            background: {{ $cat['color'] }};">
                </div>
            </div>
        </div>
        <div class="cat-amount">{{ number_format($cat['amount'], 0, ',', ' ') }} FCFA</div>
    </div>
    @endforeach
</div>
@endif

{{-- Tableau des transactions --}}
<p class="section-title">Détail des transactions</p>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Projet</th>
                <th>Description</th>
                <th>Catégorie</th>
                <th class="right">Montant</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr>
                <td class="mono muted">{{ $expense->expense_date?->format('d/m/Y') }}</td>
                <td>{{ $expense->project?->name ?? '—' }}</td>
                <td>{{ \Str::limit($expense->description, 40) }}</td>
                <td class="muted">{{ $expense->category?->name ?? '—' }}</td>
                <td class="right mono">{{ number_format($expense->amount, 0, ',', ' ') }} FCFA</td>
                <td>
                    @if($expense->status === 'validated')
                        <span class="badge badge-validated">Validée</span>
                    @elseif($expense->status === 'pending')
                        <span class="badge badge-pending">En attente</span>
                    @else
                        <span class="badge badge-rejected">Rejetée</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:16px; color:#717c82;">
                    Aucune transaction ce mois-ci
                </td>
            </tr
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pied de page --}}
<div class="doc-footer">
    <div class="left">{{ $company }} · Confidentiel</div>
    <div class="right">{{ $title }}</div>
</div>

</body>
</html>