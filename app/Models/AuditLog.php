<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'before',
        'after',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    public static function log($action, $modelType = null, $modelId = null, $before = null, $after = null)
    {
        try {
            self::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'before' => $before,
                'after' => $after,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to write audit log: ' . $e->getMessage());
        }
    }

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    /**
     * Relasi ke User (pelaku aksi)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
