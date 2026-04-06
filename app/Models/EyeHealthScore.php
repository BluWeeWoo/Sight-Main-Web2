<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EyeHealthScore extends Model
{
    protected $table = 'eye_health_score';
    protected $primaryKey = 'score_id';
    public $timestamps = false;

    protected $fillable = [
        'child_id',
        'daily_score',
        'grade',
        'recorded_date',
    ];

    protected $casts = [
        'recorded_date' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(ChildProfile::class, 'child_id');
    }
}
