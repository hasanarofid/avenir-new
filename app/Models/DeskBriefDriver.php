<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeskBriefDriver extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'affected_sectors_json' => 'array',
        'affected_tickers_json' => 'array',
    ];

    public function brief()
    {
        return $this->belongsTo(DeskBrief::class, 'brief_id');
    }
}
