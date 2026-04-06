<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildProfile extends Model
{
    protected $table = 'child_profile';
    protected $primaryKey = 'child_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'birthdate',
        'device_id',
        'last_sync',
        'login_code',
        'calibration_baseline',
        'fcm_token',
        'updated_at',
    ];

    protected $casts = [
        'last_sync' => 'datetime',
        'updated_at' => 'datetime',
        'calibration_baseline' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guardians()
    {
        return $this->belongsToMany(GuardianProfile::class, 'guardian_child_link', 'child_id', 'guardian_id');
    }

    public function doctors()
    {
        return $this->belongsToMany(DoctorProfile::class, 'clinician_patient_link', 'child_id', 'doctor_id');
    }

    public function eyeHealthMetrics()
    {
        return $this->hasMany(EyeHealthMetrics::class, 'child_id');
    }

    public function eyeHealthScores()
    {
        return $this->hasMany(EyeHealthScore::class, 'child_id');
    }

    public function sessionLimits()
    {
        return $this->hasOne(SessionLimits::class, 'child_id');
    }

    public function inventory()
    {
        return $this->hasMany(ChildInventory::class, 'child_id');
    }

    public function pet()
    {
        return $this->hasOne(VirtualPet::class, 'child_id');
    }

    public function doctorLinks()
    {
        return $this->hasMany(ClinicianPatientLink::class, 'child_id');
    }
}
