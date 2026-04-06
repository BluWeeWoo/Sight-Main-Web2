<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualPet extends Model
{
    protected $table = 'virtual_pet';
    protected $primaryKey = 'pet_id';
    public $timestamps = false;

    protected $fillable = [
        'child_id',
        'pet_state',
        'currency',
        'xp_points',
        'current_streak_days',
        'last_streak_date',
        'updated_at',
    ];

    protected $casts = [
        'last_streak_date' => 'date',
        'updated_at' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(ChildProfile::class, 'child_id');
    }
}
