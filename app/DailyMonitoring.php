<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyMonitoring extends Model
{
    protected $table = 'daily_monitorings';

    protected $fillable = [
        'id_project',
        'site_environment_name',
        'report_name',
        'report_datetime',
        'total_server',
        'server_on',
        'offline',
        'total_server_off',
        'health_indicator',
        'server_warning',
        'alert_status',
        'status',
        'farm',
        'total_machine_farm',
        'status_machine',
        'images',
        'user_id',
    ];

    protected $dates = [
        'report_datetime',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project', 'id_project');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function projectCases()
    {
        return $this->hasMany(MCase::class, 'id_project', 'id_project');
    }
}
