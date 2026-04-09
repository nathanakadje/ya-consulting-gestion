<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ActivityLog extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'loggable_id',
        'loggable_type',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loggable()
    {
        return $this->morphTo();
    }

    /**
     * Méthode statique pratique pour logger une action
     * Usage: ActivityLog::log('created_project', $project, 'Projet créé');
     */
    public static function log(string $action, $model = null, string $description = '', array $old = [], array $new = []): void
    {
        self::create([
            // 'user_id'       => auth()->id(),
            'action'        => $action,
            'description'   => $description,
            'loggable_id'   => $model?->id,
            'loggable_type' => $model ? get_class($model) : null,
            'old_values'    => $old ?: null,
            'new_values'    => $new ?: null,
            'ip_address'    => request()->ip(),
        ]);
    }
}
