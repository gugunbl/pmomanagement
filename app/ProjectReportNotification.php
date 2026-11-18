<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectReportNotification extends Model
{
    protected $fillable = [
        'project_report_id',
        'notification_type',
        'trigger_date',
        'recipient_snapshot',
        'status',
        'attempts',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'trigger_date' => 'date',
        'recipient_snapshot' => 'array',
        'attempts' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(ProjectReport::class, 'project_report_id');
    }
}
