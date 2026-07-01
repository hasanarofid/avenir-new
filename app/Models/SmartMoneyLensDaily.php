<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartMoneyLensDaily extends Model
{
    use HasFactory;

    protected $table = 'smart_money_lens_daily';

    protected $fillable = [
        'date',
        'ticker',
        'type',
        'price_change_5d',
        'flow_z_score',
        'notes',
    ];
}
