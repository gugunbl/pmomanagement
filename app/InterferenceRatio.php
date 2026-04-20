<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InterferenceRatio extends Model
{
    protected $fillable = [
        'id_project',
        'tanggal',
        'ap_name',
        'channel_before',
        'channel_after',
        'utilisasi_before',
        'utilisasi_after',
        'waktu',
        'keterangan',
        'user_id'
    ];

    public function project()
    {
        return $this->belongsTo(\App\Project::class, 'id_project', 'id_project');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
