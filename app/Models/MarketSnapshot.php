<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketSnapshot extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'sparkline_json' => 'array',
        'last_sync' => 'datetime',
        'value' => 'decimal:4',
        'change_abs' => 'decimal:4',
        'change_pct' => 'decimal:4',
    ];
}
