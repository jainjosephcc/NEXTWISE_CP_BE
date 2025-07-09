<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffLoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'ip_address',
        'attempted_at',
    ];

    public $timestamps = false; // If you don't want created_at and updated_at

    public function staffUser()
    {
        return $this->belongsTo(StaffUser::class, 'staff_id');
    }

}
