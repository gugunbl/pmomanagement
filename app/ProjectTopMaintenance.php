<?php

// App/ProjectTopMaintenance.php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectTopMaintenance extends Model
{
    protected $table = 'project_top_maintenance';

    protected $fillable = [
        'project_id',
        'top_ke',
        'periode_start',
        'periode_end',
        'status',
        'keterangan',
        'signers'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id_project');
    }

    public function comments()
    {
        return $this->hasMany(ProjectTopMaintenanceComment::class, 'project_top_maintenance_id', 'id')->orderBy('created_at', 'desc');
    }

    public function statusLogs()
    {
        return $this->hasMany(ProjectTopMaintenanceStatusLog::class, 'project_top_maintenance_id', 'id')->orderBy('created_at', 'desc');
    }
}
