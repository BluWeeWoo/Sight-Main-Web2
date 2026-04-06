<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'action_taken',
        'target_entity',
        'ip_address',
    ];

    public function admin()
    {
        return $this->belongsTo(AdminProfile::class, 'admin_id');
    }
}
