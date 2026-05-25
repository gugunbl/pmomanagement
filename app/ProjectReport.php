<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectReport extends Model
{
    protected $table = 'project_reports';

    protected $fillable = [
        'project_id',
        'report_ke',
        'period_start',
        'period_end',
        'submission_date',
        'status',
        'keterangan',
        'uploaded_file',
        'uploaded_disk',
        'uploaded_original_name',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id_project');
    }

    public function comments()
    {
        return $this->hasMany(ProjectReportComment::class, 'project_report_id', 'id')->orderBy('created_at', 'desc');
    }

    public function statusLogs()
    {
        return $this->hasMany(ProjectReportStatusLog::class, 'project_report_id', 'id')->orderBy('created_at', 'desc');
    }
}
