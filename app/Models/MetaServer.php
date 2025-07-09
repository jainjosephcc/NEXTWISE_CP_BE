<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaServer extends Model
{
    protected $table = 'tb_meta_servers';

    protected $fillable = [
        'company_name',
        'server_name',
        'server_ip',
        'server_port',
        'api_url',
        'server_type',
        'ssl_enabled',
        'manager_id',
        'status',
        'description',
        'created_by',
        'updated_by',
        'is_synced',          // Add this line
        'last_synced',        // Add this line
    ];

    // Relationships

    public function manager()
    {
        return $this->belongsTo(StaffUser::class, 'manager_id');
    }

    public function creator()
    {
        return $this->belongsTo(StaffUser::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(StaffUser::class, 'updated_by');
    }


}
