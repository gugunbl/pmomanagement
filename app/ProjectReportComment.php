<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectReportComment extends Model
{
    protected $table = 'project_report_comments';

    protected $fillable = [
        'project_report_id',
        'user_id',
        'comment',
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
