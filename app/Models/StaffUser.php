<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class StaffUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'tb_staff_users';

    protected $fillable = [
        'staff_name',
        'email',
        'password',
        'contact_no',
        'group_id',
        'active',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(StaffGroup::class, 'group_id');
    }

    public function loginAttempts()
    {
        return $this->hasMany(StaffLoginAttempt::class, 'staff_id');
    }

    public function accessLogs()
    {
        return $this->hasMany(UserAccessLog::class, 'user_id');
    }

    /**
     * Get the master associated with the slave.
     */
    public function master()
    {
        return $this->belongsTo(Master::class, 'master_id');
    }

    /**
     * Get the server associated with the slave.
     */
    public function server()
    {
        return $this->belongsTo(Master::class, 'server_id');
    }

    /**
     * Get the user who mapped the slave.
     */
    public function mappedByUser()
    {
        return $this->belongsTo(User::class, 'mapped_by');
    }
}
