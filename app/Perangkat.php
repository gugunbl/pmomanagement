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
        'start_warranty',    
        'end_warranty',
        'keterangan',
        'tgl_online',
        'user_id',
        'created_at',
        'updated_at',
        ];

     /**
     * Relasi ke Project
     */
    public function project()
    {
        // Asumsi foreign key: id_project, local key: id_project di tabel m_project
        return $this->belongsTo(Project::class, 'id_project', 'id_project');
    }
}
