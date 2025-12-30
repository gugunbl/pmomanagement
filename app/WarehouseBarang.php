<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseBarang extends Model
{
    protected $table = 't_warehouse_barang';
    protected $primaryKey = 'id_tbarang_warehouse';

    protected $fillable = [
        'no_dokumen',
        'id_project',
        'serialnumber',
        'nama_barang',
        'quantity',
        'deskripsi',
        'user_id'
    ];
}
