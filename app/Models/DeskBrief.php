<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeskBrief extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'published_at' => 'datetime',
    ];

    public function marketStance()
    {
        return $this->belongsTo(MarketStanceDaily::class, 'market_stance_id');
    }

    public function analyst()
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }

    public function drivers()
    {
        return $this->hasMany(DeskBriefDriver::class, 'brief_id')->orderBy('rank');
    }

    public function radarStocks()
    {
        return $this->hasMany(RadarStock::class, 'brief_id')->orderBy('priority');
    }
}
