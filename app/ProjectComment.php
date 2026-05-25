<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectComment extends Model
{
    protected $table = 'project_comments';

    protected $fillable = [
        'project_id',
        'user_id',
        'comment',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id_project');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
