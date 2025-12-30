<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jenisperangkat extends Model
{
    //
    protected $table = 'm_jenis_perangkat';
    protected $fillable = ['id_jenis','jns_perangkat'];

    protected static $logFillable = true;
    protected static $logName = 'jenisperangkat';
    protected static $logOnlyDirty = true;


}
