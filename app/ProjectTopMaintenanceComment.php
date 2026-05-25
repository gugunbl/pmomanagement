<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectTopMaintenanceComment extends Model
{
    protected $table = 'project_top_maintenance_comments';

    protected $fillable = [
        'project_top_maintenance_id',
        'user_id',
        'comment',
    ];

    public function ba()
    {
        return $this->belongsTo(ProjectTopMaintenance::class, 'project_top_maintenance_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
