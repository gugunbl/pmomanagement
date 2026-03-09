<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MSales extends Model
{
    protected $table = 'm_sales';
    protected $fillable = ['name', 'status'];
}
