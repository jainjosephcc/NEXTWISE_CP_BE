<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master extends Model
{
    use HasFactory;

    protected $table = 'tb_masters';

    protected $fillable = [
        'mc_name',
        'mc_mt5_id',
        'server_id',
        'mapped_by',
        'performance_matrix',
        'risk_factor',
        'is_config_identical',
        'risk_approach',
        'commission_percentage',
        'commission_type',
        'lot_size',
        'multiplier',
        'fixed_balance',
        'copy_sl',
        'copy_tp',
        'is_reverse',
        'status',
        'is_live',
        'created_by',
        'updated_by',
        'ex_client_id',
        'ex_client_name',
        'ex_client_email',
    ];

    /**
     * The meta server that this master is associated with.
     */
    public function server()
    {
        return $this->belongsTo(MetaServer::class, 'server_id');
    }

    /**
     * The staff user who mapped this master.
     */
    public function mapper()
    {
        return $this->belongsTo(StaffUser::class, 'mapped_by');
    }

    /**
     * The staff user who created this master.
     */
    public function creator()
    {
        return $this->belongsTo(StaffUser::class, 'created_by');
    }

    /**
     * The staff user who last updated this master.
     */
    public function updater()
    {
        return $this->belongsTo(StaffUser::class, 'updated_by');
    }

    /**
     * Get the slaves where this master is the primary master.
     */
    public function slaves()
    {
        return $this->hasMany(Slave::class, 'master_id');
    }

    /**
     * Get the slaves where this master is the server.
     */
    public function slaveServers()
    {
        return $this->hasMany(Slave::class, 'server_id');
    }
}
