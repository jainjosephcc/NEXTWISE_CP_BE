<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken;

class UserAccessLog extends Model
{
    use HasFactory;
    protected $table = 'user_access_logs';

    protected $fillable = [
        'user_type',
        'type_of_user',
        'personal_access_token_id',
        'user_id',
        'ip_address',
        'asn_organization',
    ];

    public function token()
    {
        return $this->belongsTo(PersonalAccessToken::class, 'personal_access_token_id');
    }

    public function user()
    {
        return $this->belongsTo(StaffUser::class, 'user_id');
    }
}
