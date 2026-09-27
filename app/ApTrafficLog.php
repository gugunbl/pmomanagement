<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApTrafficLog extends Model
{
    protected $table = 'ap_traffic_logs';

    protected $fillable = [
        'id_project',
        'id_lokasi',
        'ap_floor_id',
        'nama_lantai',
        'tanggal',
        'traffic_value',
        'traffic_unit',
        'traffic_in_mb',
        'jumlah_user',
        'user_id',
        'keterangan',
    ];

    protected $dates = [
        'tanggal',
    ];

    /**
     * Boot helper to normalize traffic to MB before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $val = floatval($model->traffic_value);
            $unit = strtoupper(trim($model->traffic_unit));

            switch ($unit) {
                case 'KB':
                    $model->traffic_in_mb = $val / 1024;
                    break;
                case 'MB':
                    $model->traffic_in_mb = $val;
                    break;
                case 'GB':
                    $model->traffic_in_mb = $val * 1024;
                    break;
                case 'TB':
                    $model->traffic_in_mb = $val * 1024 * 1024;
                    break;
                default:
                    $model->traffic_in_mb = $val;
                    break;
            }
        });
    }

    /**
     * Relasi ke Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project', 'id_project');
    }

    /**
     * Relasi ke Lokasi
     */
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'id_lokasi', 'id_lokasi');
    }

    /**
     * Relasi ke Floor
     */
    public function floor()
    {
        return $this->belongsTo(ApFloor::class, 'ap_floor_id');
    }

    /**
     * Relasi ke User yang menginput
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor formatted traffic, e.g. 10.55MB or 1.28 GB
     */
    public function getFormattedTrafficAttribute()
    {
        if ($this->traffic_value === null || $this->traffic_value === '') {
            return '-';
        }
        $val = rtrim(rtrim(number_format((float)$this->traffic_value, 2, '.', ''), '0'), '.');
        return $val . ' ' . $this->traffic_unit;
    }
}
