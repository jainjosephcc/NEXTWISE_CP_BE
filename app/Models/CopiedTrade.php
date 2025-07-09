<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CopiedTrade extends Model
{
    use SoftDeletes;

    protected $table = 'copied_trades';

    protected $fillable = [
        'master_login',
        'slave_login',
        'symbol',
        'trade_type',
        'volume',
        'price',
        'profit',
        'status',
        'comment',
    ];

    protected $casts = [
        'volume' => 'float',
        'price' => 'float',
        'profit' => 'float',
        'master_login' => 'integer',
        'slave_login' => 'integer',
    ];
}
