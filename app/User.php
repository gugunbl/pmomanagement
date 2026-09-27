<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasRoles, LogsActivity, ThrottlesLogins;
    protected static $ignoreChangedAttributes = ['password'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone_number', 'profile_photo', 'status', 'id_project'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected static $logFillable = true;
    protected static $logName = 'user';
    protected static $logOnlyDirty = true;
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setStatusAttribute($status)
    {
        $this->attributes['status'] = ($status)? 1 : 0;
    }
    public function setPasswordAttribute($password)
    {
        if ($password) {
            $this->attributes['password'] = \Illuminate\Support\Facades\Crypt::encryptString($password);
        }
    }
    public function categories()
    {
        return $this->hasMany('App\Category');
    }
    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'id_project');
    }

    public function isSuperAdmin()
    {
        if ($this->relationLoaded('roles')) {
            $roleNames = $this->roles->pluck('name')->all();
            if (collect($roleNames)->contains(fn ($name) => strtolower((string) $name) === 'super-admin')) {
                return true;
            }
        }

        if (!method_exists($this, 'hasRole')) {
            return false;
        }

        try {
            return $this->hasRole('super-admin');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function canEditProject(Project $project)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $pmImplementasiUserId = $project->mPMImplementasi ? $project->mPMImplementasi->user_id : null;
        $pmMaintenanceUserId = $project->mPMMaintenance ? $project->mPMMaintenance->user_id : null;

        $currentUserId = (int) ($this->id ?? 0);

        return $currentUserId > 0 && (
            (int) $currentUserId === (int) ($pmImplementasiUserId ?? 0) ||
            (int) $currentUserId === (int) ($pmMaintenanceUserId ?? 0)
        );
    }

    public function teamSupports()
    {
        return $this->belongsToMany(TeamSupport::class, 'user_team_support', 'user_id', 'team_support_id');
    }

    public function caseComments()
    {
        return $this->hasMany(CaseComment::class, 'user_id', 'id');
    }
}
