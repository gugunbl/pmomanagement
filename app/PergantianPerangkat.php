<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PergantianPerangkat extends Model
{
    protected $table = 't_pergantian_perangkat';

    protected $primaryKey = 'id_pergantian';

    protected $fillable = [
        'id_perangkat_lama',
        'id_perangkat_baru',
        'jenis_penggantian',
        'alasan_penggantian',
        'tanggal_pergantian',
        'user_pelapor_id',
        'user_pelapor_nama',
        'user_pelapor_jabatan',
        'user_approval_id',
        'user_approval_nama',
        'user_approval_jabatan',
        'status'
    ];

    // 🔹 RELASI PERANGKAT LAMA
    public function perangkatLama()
    {
        return $this->belongsTo(
            Perangkat::class,
            'id_perangkat_lama',
            'id_perangkat'
        );
    }

    // 🔹 RELASI PERANGKAT BARU
    public function perangkatBaru()
    {
        return $this->belongsTo(
            Perangkat::class,
            'id_perangkat_baru',
            'id_perangkat'
        );
    }

}
