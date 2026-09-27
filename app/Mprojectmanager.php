<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Mprojectmanager extends Model
{
    protected $table = 'm_project_manager';
    protected $primaryKey = 'id_pm';

    protected $fillable = [
        'id_pm',
        'nama_pm',
        'status',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($pm) {
            if (empty($pm->user_id)) {
                return;
            }

            $userId = (int) $pm->user_id;

            if (!User::whereKey($userId)->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => 'User ID tidak valid. Pastikan user sudah terdaftar di user management.'
                ]);
            }

            $duplicateExists = self::where('user_id', $userId)
                ->whereKeyNot($pm->getKey())
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'user_id' => 'User ini sudah dipakai sebagai Project Manager lain. User ID harus unik.'
                ]);
            }
        });
    }
}
