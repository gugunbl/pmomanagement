<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamSupport extends Model
{
    protected $table = 'm_team_supports';

    protected $fillable = [
        'name',
        'status',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_team_support', 'team_support_id', 'project_id');
    }
}
