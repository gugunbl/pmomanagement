<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $table = 't_warehouse';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'no_dokumen',
        'id_project',
        'jenis_dokumen',
        'tgl_dokumen',
        'nama_pemohon',
        'nama_perusahaan',
        'departemen',
        'jabatan',
        'po_do',
        'nomor_po_do',
        'lokasi_tujuan_asal',
        'tgl_masuk_keluar',
        'jam_masuk_keluar',
        'sesuai_project',
        'catatan',
        'upload_po',
        'upload_do',
        'upload_st',
        'user_id'
    ];

    public function barang()
    {
        return $this->hasMany(WarehouseBarang::class, 'no_dokumen', 'no_dokumen');
    }
}
