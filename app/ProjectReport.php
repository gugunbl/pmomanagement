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
        'keterangan'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id_project');
    }
}
