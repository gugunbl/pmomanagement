<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mprojectmanager extends Model
{
    //
        //
    protected $table = 'm_project_manager';
    protected $primaryKey = 'id_pm';
    
    protected $fillable = [
        'id_pm',
        'nama_pm',
        'status',
    ];

}
