<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EyeHealthMetrics extends Model
{
    protected $table = 'eye_health_metrics';
    protected $primaryKey = 'metric_id';
    public $timestamps = false;

    protected $fillable = [
        'child_id',
        'avg_blink_rate',
        'avg_distance',
        'strain_events',
        'timestamp',
        'screen_time_minutes',
        'health_score',
        'coins',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(ChildProfile::class, 'child_id');
    }
}
