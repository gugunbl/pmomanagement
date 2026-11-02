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
        'ba_number',
        'ba_date',
        'ba_origin_location',
        'ba_destination_location',
        'ba_customer_pic_name',
        'ba_customer_pic_title',
        'ba_executor_pic_name',
        'ba_executor_pic_title',
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

    public function comments()
    {
        return $this->hasMany(CaseComment::class, 'case_id', 'id_case')->orderBy('created_at', 'desc');
    }
}
