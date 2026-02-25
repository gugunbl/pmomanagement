<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Perangkat extends Model
{
    //
    protected $table = 'm_perangkat';
    protected $primaryKey = 'id_perangkat'; 

        protected $fillable = [
        'id_perangkat',
        'id_project',
        'jenis',
        'merk',
        'type',
        'serialnumber',
        'status',
        'site',
        'lantai',
        'dimensi',
        'koordinat',
        'id_lokasi',
        'status_server',
        'id_warehouse_transaksi',
        'start_warranty',    
        'end_warranty',
        'keterangan',
        'upload_do',
        'type_lisensi',
        'tgl_online',
        'user_id',
        'created_at',
        'updated_at',
        ];

     /**
     * Relasi ke Lokasi
     */
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'id_lokasi', 'id_lokasi');
    }

    /**
     * Relasi ke Jenis Perangkat
     */
    public function jenisPerangkat()
    {
        return $this->belongsTo(Jenisperangkat::class, 'jenis', 'id_jenis');
    }

     /**
     * Relasi ke Project
     */
    public function project()
    {
        // Asumsi foreign key: id_project, local key: id_project di tabel m_project
        return $this->belongsTo(Project::class, 'id_project', 'id_project');
    }
}
