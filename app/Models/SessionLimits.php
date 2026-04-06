<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionLimits extends Model
{
    protected $table = 'session_limits';
    protected $primaryKey = 'limit_id';
    public $timestamps = false;

    protected $fillable = [
        'child_id',
        'daily_limit_minutes',
        'mode',
        'is_active',
        'harmful_distance_threshold',
        'critical_distance_threshold',
        'auto_enforce_breaks',
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_enforce_breaks' => 'boolean',
        'updated_at' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(ChildProfile::class, 'child_id');
    }
}
