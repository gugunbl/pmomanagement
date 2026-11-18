<?php

// App/ProjectTopMaintenance.php

namespace App;

use App\Support\NextcloudFilename;
use Carbon\Carbon;
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
        'signers',
        'uploaded_file',
        'uploaded_disk',
        'uploaded_original_name',
        'uploaded_at',
        'uploaded_by',
    ];

    protected $casts = [
        'periode_start' => 'date',
        'periode_end' => 'date',
        'uploaded_at' => 'datetime',
    ];

    public function uploadFilename($uploadDate = null)
    {
        $projectName = NextcloudFilename::projectName($this->project ? $this->project->nama_project : '');

        return implode('_', [
            'BA',
            'TOP' . $this->top_ke,
            $projectName,
            Carbon::parse($uploadDate ?: Carbon::now())->format('Ymd'),
        ]) . '.pdf';
    }

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
