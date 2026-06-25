<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadarStock extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'target_price' => 'decimal:2',
    ];

    public function brief()
    {
        return $this->belongsTo(DeskBrief::class, 'brief_id');
    }
}
