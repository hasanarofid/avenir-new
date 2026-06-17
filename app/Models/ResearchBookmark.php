<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchBookmark extends Model
{
    protected $fillable = [
        'research_id',
        'user_id',
    ];

    public function research(): BelongsTo
    {
        return $this->belongsTo(Research::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
