<?php

namespace App;

use Carbon\Carbon;
use App\Support\NextcloudFilename;
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

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'submission_date' => 'date',
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
        $projectName = NextcloudFilename::projectName($project ? $project->nama_project : '');

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

    /**
     * Keep the physical object name descriptive while applying a safe project
     * name limit for Nextcloud and other filesystems.
     */
    public function storageFilename($uploadDate = null)
    {
        return $this->uploadFilename($uploadDate);
    }

    public function comments()
    {
        return $this->hasMany(ProjectReportComment::class, 'project_report_id', 'id')->orderBy('created_at', 'desc');
    }

    public function statusLogs()
    {
        return $this->hasMany(ProjectReportStatusLog::class, 'project_report_id', 'id')->orderBy('created_at', 'desc');
    }

    public function notifications()
    {
        return $this->hasMany(ProjectReportNotification::class, 'project_report_id', 'id')
            ->orderByDesc('created_at');
    }

    public function latestPeriodEndNotification()
    {
        if ($this->relationLoaded('notifications')) {
            return $this->notifications
                ->first(function ($notification) {
                    return $notification->notification_type === 'period_end';
                });
        }

        return $this->notifications()
            ->where('notification_type', 'period_end')
            ->first();
    }
}
