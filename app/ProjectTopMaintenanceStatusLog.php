<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectTopMaintenanceStatusLog extends Model
{
    protected $table = 'project_top_maintenance_status_logs';

    protected $fillable = [
        'project_top_maintenance_id',
        'user_id',
        'old_status',
        'new_status',
    ];

    public function ba()
    {
        return $this->belongsTo(ProjectTopMaintenance::class, 'project_top_maintenance_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
