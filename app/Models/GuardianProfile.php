<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardianProfile extends Model
{
    protected $table = 'guardian_profile';
    protected $primaryKey = 'guardian_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'contact_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function children()
    {
        return $this->belongsToMany(ChildProfile::class, 'guardian_child_link', 'guardian_id', 'child_id');
    }
}
