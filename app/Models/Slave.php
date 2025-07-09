<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slave extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tb_slaves';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'master_id',
        'sl_mt5_id',
        'server_id',
        'mapped_by',
        'is_config_unique',
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
        'ex_client_id',
        'ex_client_name',
        'ex_client_email',
    ];

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
