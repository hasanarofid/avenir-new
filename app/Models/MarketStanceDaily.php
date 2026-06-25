<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketStanceDaily extends Model
{
    use HasFactory;

    protected $table = 'market_stance_daily';
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function deskBrief()
    {
        return $this->hasOne(DeskBrief::class, 'market_stance_id');
    }
}
