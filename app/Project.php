<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
    protected $table = 'm_project';
    protected $primaryKey = 'id_project'; // Jika primary key bukan 'id'
    
    protected $fillable = [
        'id_project',
        'tahun',
        'nama_project',
        'nomor_spk',
        'tanggal_spk',
        'status_implementation',
        'customer',
        'divisi',
        'tanggal_kom',
        'tanggal_akhir_implementasi',
        'status_maintenance',
        'tanggal_mulai_maintenance',
        'tanggal_akhir_maintenance',
        'link_dokumen',
        'pm_implementasi',
        'pm_maintenance',
        'technical_writer',
        'category',
        'sales',
        'team',
        'keterangan',
        'top_impl_total',
        'top_maintenance_total',
        'project_report_total',
    ];
    
    /**
     * Relasi ke Perangkat
     */
    public function perangkats()
    {
        return $this->hasMany(Perangkat::class, 'id_project', 'id_project');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user', 'id_project', 'user_id');
    }

// App/Project.php

public function topMaintenance()
{
    return $this->hasMany(
        \App\ProjectTopMaintenance::class,
        'project_id',
        'id_project'
    );
}


    
    public function topImplementasi()
{
    return $this->hasMany(
        \App\ProjectTopImplementasi::class,
        'project_id',
        'id_project'
    );
}

    public function reports()
    {
        return $this->hasMany(
            \App\ProjectReport::class,
            'project_id',
            'id_project'
        );
    }


    public function mPMImplementasi()
    {
        return $this->belongsTo(Mprojectmanager::class,  'pm_implementasi', 'id_pm')
        ->where('status',1);
        // atau return $this->belongsTo(Category::class);
    }

    public function mPMMaintenance()
    {
        return $this->belongsTo(Mprojectmanager::class,  'pm_maintenance', 'id_pm')
        ->where('status',1);
        // atau return $this->belongsTo(Category::class);
    }


    public function mcategory()
    {
        return $this->belongsTo(Category::class,  'category', 'id')
        ->where('status',1);
        // atau return $this->belongsTo(Category::class);
    }

    public function statusMaintenance()
    {
        return $this->belongsTo(Status::class, 'status_maintenance', 'id_status')
        ->where('is_type',1);//filter is_type
    }
    
    /**
     * Relasi ke Status (untuk status implementation)
     */
    public function statusImplementation()
    {
        return $this->belongsTo(Status::class, 'status_implementation', 'id_status')
        ->where('is_type', 1); // <- Tambahkan ini
    }

    public function m_status()
    {
        return $this->belongsTo(MStatus::class, 'status_implementation', 'id_status')
                    ->where('is_type', 1);
    }

}

