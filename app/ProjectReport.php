<?php

namespace App;

use Carbon\Carbon;
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

    public function uploadFilename($uploadDate = null)
    {
        if (!$this->period_start || !$this->period_end) {
            return null;
        }

        $project = $this->project;
        $projectName = $project ? (string) $project->nama_project : '';
        $projectName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $projectName);
        $projectName = preg_replace('/[\x00-\x1F\x7F]/u', '', $projectName);
        $projectName = preg_replace('/\s+/u', ' ', trim($projectName));
        $projectName = trim($projectName, " .");
        $projectName = $projectName === '' ? 'Project' : mb_substr($projectName, 0, 180, 'UTF-8');

        return implode('_', [
            Carbon::parse($uploadDate ?: Carbon::now())->format('Ymd'),
            'Laporan',
            'PM',
            $projectName,
            $this->report_ke,
            Carbon::parse($this->period_start)->format('Ymd'),
            Carbon::parse($this->period_end)->format('Ymd'),
        ]) . '.pdf';
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
