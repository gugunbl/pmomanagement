<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectReportStatusLog extends Model
{
    protected $table = 'project_report_status_logs';

    protected $fillable = [
        'project_report_id',
        'user_id',
        'old_status',
        'new_status',
    ];

    public function report()
    {
        return $this->belongsTo(ProjectReport::class, 'project_report_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
