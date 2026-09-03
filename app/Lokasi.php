<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    protected $table = 'm_lokasi';
    protected $primaryKey = 'id_lokasi';

    protected $fillable = [
        'nama_lokasi',
        'alamat',
        'keterangan',
        'user_id'
    ];

    public function perangkats()
    {
        return $this->hasMany(Perangkat::class, 'id_lokasi', 'id_lokasi');
    }

    public function apFloors()
    {
        return $this->hasMany(ApFloor::class, 'id_lokasi', 'id_lokasi')->orderBy('urutan', 'asc')->orderBy('nama_lantai', 'asc');
    }

    public function apTrafficLogs()
    {
        return $this->hasMany(ApTrafficLog::class, 'id_lokasi', 'id_lokasi');
    }
}
