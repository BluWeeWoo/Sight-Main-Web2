<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicianPatientLink extends Model
{
    protected $table = 'clinician_patient_link';
    protected $primaryKey = 'link_id';
    public $timestamps = false;

    protected $fillable = [
        'doctor_id',
        'child_id',
        'linkage_key',
        'is_active',
        'linkage_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'linkage_date' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }

    public function child()
    {
        return $this->belongsTo(ChildProfile::class, 'child_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'link_id');
    }
}
