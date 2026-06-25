<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectorBiasDaily extends Model
{
    use HasFactory;

    protected $table = 'sector_bias_daily';
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'flow_value' => 'decimal:2',
        'return_1d' => 'decimal:4',
    ];
}
