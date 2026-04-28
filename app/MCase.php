<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MCase extends Model
{
    protected $table = 'm_cases';
    protected $primaryKey = 'id_case';

    protected $fillable = [
        'id_project',
        'user_id',
        'type',
        'open_date',
        'closed_date',
        'subject',
        'detail',
        'reporter_name',
        'reporter_contact',
        'ncm_customer',
        'severity',
        'affected_sn',
        'location',
        'principal_ticket_no',
        'resolution_detail',
        'status',
        'evidence_photo',
        'rca_file',
        'mop_file',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project', 'id_project');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function perangkat()
    {
        // Many cases can belong to one device (SN)
        return $this->belongsTo(Perangkat::class, 'affected_sn', 'serialnumber');
    }
}
