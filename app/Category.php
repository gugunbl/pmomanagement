<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{    
    protected $table = 'categories';
    protected $primaryKey = 'id'; // Jika primary key bukan 'id'

    use LogsActivity;

    protected $fillable = ['category_name','id','status','user_id'];

    protected static $logFillable = true;
    protected static $logName = 'category';
    protected static $logOnlyDirty = true;



    /**
     * Relasi ke Project (untuk category project)
     */
    public function CategoryProject()
    {
        return $this->hasMany(Project::class, 'category', 'id');
    }


    public function setStatusAttribute($status)
    {
        $this->attributes['status'] = ($status)? 1 : 0;
    }
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function posts()
    {
        return $this->hasMany('App\Post');
    }
}
