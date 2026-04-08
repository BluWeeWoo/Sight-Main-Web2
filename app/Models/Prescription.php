<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $table = 'prescription';
    protected $primaryKey = 'recommendation_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'recommendation_id',
        'link_id',
        'advice_text',
        'date_issued',
    ];

    protected $casts = [
        'date_issued' => 'datetime',
    ];

    public function link()
    {
        return $this->belongsTo(ClinicianPatientLink::class, 'link_id');
    }
}
