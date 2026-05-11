<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CaseComment extends Model
{
    protected $table = 'm_case_comments';
    protected $fillable = ['case_id', 'user_id', 'comment'];

    public function case()
    {
        return $this->belongsTo(MCase::class, 'case_id', 'id_case');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
