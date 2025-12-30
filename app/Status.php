<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    //
    protected $table = 'm_status';
    protected $primaryKey = 'id_status';
    
    protected $fillable = [
        'id_status',
        'nama_status',
        'is_type',
    ];
    
    /**
     * Relasi ke Project (untuk status maintenance)
     */
    public function projectsMaintenance()
    {
        return $this->hasMany(Project::class, 'status_maintenance', 'id_status');
    }
    
    /**
     * Relasi ke Project (untuk status implementation)
     */
    public function projectsImplementation()
    {
        return $this->hasMany(Project::class, 'status_implementation', 'id_status');
    }
}
