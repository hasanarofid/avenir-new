<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchViewLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'research_id',
        'user_id',
        'ip_address',
        'created_at',
    ];

    public function research(): BelongsTo
    {
        return $this->belongsTo(Research::class);
    }
}
