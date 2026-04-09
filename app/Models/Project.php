<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'reference',
        'description',
        'client_id',
        'created_by',
        'project_lead_id',
        'budget',
        'budget_main_oeuvre',
        'budget_materiel',
        'budget_transport',
        'budget_autres',
        'start_date',
        'end_date_planned',
        'end_date_actual',
        'status',
        'suppliers',
    ];

    protected function casts(): array
    {
        return [
            'start_date'       => 'date',
            'end_date_planned' => 'date',
            'end_date_actual'  => 'date',
            'budget'           => 'decimal:2',
            'suppliers'        => 'array',
        ];
    }

    
    // ── Accesseurs calculés ────────────────────────────────

    /**
     * Total des dépenses du projet
     */
    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    /**
     * Gain brut = budget - dépenses
     */
    public function getGrossGainAttribute(): float
    {
        return $this->budget - $this->total_expenses;
    }

    /**
     * Marge en pourcentage
     */
    public function getMarginPercentAttribute(): float
    {
        if ($this->budget <= 0) return 0;
        return round(($this->gross_gain / $this->budget) * 100, 2);
    }

    /**
     * Pourcentage du budget consommé
     */
    public function getBudgetUsedPercentAttribute(): float
    {
        if ($this->budget <= 0) return 0;
        return round(($this->total_expenses / $this->budget) * 100, 2);
    }

    // ── Relations ──────────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lead()
    {
        return $this->belongsTo(User::class, 'project_lead_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    // ── Scopes (filtres réutilisables) ────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'en_cours');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
