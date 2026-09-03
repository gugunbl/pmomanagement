<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApFloor extends Model
{
    protected $table = 'ap_floors';

    protected $fillable = [
        'id_project',
        'id_lokasi',
        'nama_lantai',
        'urutan',
        'keterangan',
        'user_id',
    ];

    /**
     * Relasi ke Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project', 'id_project');
    }

    /**
     * Relasi ke Lokasi
     */
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'id_lokasi', 'id_lokasi');
    }

    /**
     * Relasi ke Perangkat (Access Points) yang ada di lantai ini
     */
    public function perangkats()
    {
        return $this->belongsToMany(Perangkat::class, 'ap_floor_perangkat', 'ap_floor_id', 'id_perangkat')
                    ->withTimestamps();
    }

    /**
     * Relasi ke Traffic Logs
     */
    public function trafficLogs()
    {
        return $this->hasMany(ApTrafficLog::class, 'ap_floor_id');
    }

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
