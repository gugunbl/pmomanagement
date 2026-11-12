<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{
    protected $table = 'categories';

    protected $primaryKey = 'id'; // Jika primary key bukan 'id'

    use LogsActivity;

    protected $fillable = ['category_name', 'id', 'status', 'user_id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('category');
    }

    /**
     * Relasi ke Project (untuk category project)
     */
    public function CategoryProject()
    {
        return $this->hasMany(Project::class, 'category', 'id');
    }

    public function setStatusAttribute($status)
    {
        $this->attributes['status'] = ($status) ? 1 : 0;
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
