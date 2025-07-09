<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffGroup extends Model
{
    protected $table = 'tb_staff_groups';

    protected $fillable = [
        'group_name',
        'description',
        'status',
    ];

    public function staffUsers()
    {
        return $this->hasMany(StaffUser::class, 'group_id');
    }
}
