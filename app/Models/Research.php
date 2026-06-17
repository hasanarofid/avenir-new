<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Research extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_premium' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function viewLogs(): HasMany
    {
        return $this->hasMany(ResearchViewLog::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(ResearchBookmark::class);
    }
}
