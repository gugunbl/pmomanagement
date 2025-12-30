<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectTopImplementasi extends Model
{
    protected $table = 'project_top_implementasi';

    protected $fillable = [
        'project_id',
        'top_ke',
        'tanggal_top',
        'status',
        'berita_acara'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id_project');
    }
}
