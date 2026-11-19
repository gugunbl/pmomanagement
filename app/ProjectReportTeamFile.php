<?php

namespace App;

use App\Support\NextcloudFilename;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProjectReportTeamFile extends Model
{
    protected $fillable = [
        'project_report_id',
        'team_support_id',
        'uploaded_file',
        'uploaded_disk',
        'uploaded_original_name',
        'file_size',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(ProjectReport::class, 'project_report_id');
    }

    public function teamSupport()
    {
        return $this->belongsTo(TeamSupport::class, 'team_support_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function uploadFilename($uploadDate = null)
    {
        $this->loadMissing(['report.project', 'teamSupport']);
        $report = $this->report;

        if (!$report || !$report->period_start || !$report->period_end) {
            return null;
        }

        return implode('_', [
            Carbon::parse($uploadDate ?: Carbon::now())->format('Ymd'),
            'Laporan',
            'PM',
            NextcloudFilename::projectName(optional($report->project)->nama_project),
            $report->report_ke,
            NextcloudFilename::teamName(optional($this->teamSupport)->name),
            Carbon::parse($report->period_start)->format('Ymd'),
            Carbon::parse($report->period_end)->format('Ymd'),
        ]) . '.pdf';
    }
}
