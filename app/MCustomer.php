<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MCustomer extends Model
{
    protected $table = 'm_customers';
    protected $fillable = ['name', 'status'];
}
