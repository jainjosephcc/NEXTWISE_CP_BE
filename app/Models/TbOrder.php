<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbOrder extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'tb_orders';

    // Define which attributes can be mass-assigned
    protected $fillable = [
        'master_id',
        'slave_id',
        'mt_user_id',
        'order_id',
        'order_type',
        'order_kind',
        'symbol',
        'price',
        'volume',
        'order_status',
        'stop_loss',
        'take_profit',
        'order_date',
        'execution_date',
        'executed_price',
        'profit_loss',
        'server_id',
    ];

    // Define the attributes that should be cast to native types
    protected $casts = [
        'order_date' => 'datetime',
        'execution_date' => 'datetime',
    ];
}
