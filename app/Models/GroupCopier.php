<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCopier extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'group_copiers';

    // Define the fillable attributes
    protected $fillable = [
        'master_id',
        'group_name',
        'server_id',
        'mapped_by',
        'is_config_unique',
        'commission_percentage',
        'commission_type',
        'risk_approach',
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

    // Define any relationships if applicable (examples shown below)
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
