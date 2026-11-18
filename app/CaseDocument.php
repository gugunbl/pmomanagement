<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CaseDocument extends Model
{
    const TYPE_EVIDENCE = 'evidence';
    const TYPE_RCA = 'rca';
    const TYPE_MOP = 'mop';

    protected $fillable = [
        'case_id',
        'document_type',
        'disk',
        'path',
        'original_name',
        'stored_name',
        'mime_type',
        'file_size',
        'checksum',
        'version',
        'is_active',
        'status',
        'error_message',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
        'is_active' => 'boolean',
    ];

    public function caseModel()
    {
        return $this->belongsTo(MCase::class, 'case_id', 'id_case');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
