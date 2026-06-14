<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function emiten()
    {
        return $this->belongsTo(Ticker::class, 'kode', 'symbol');
    }
}
