<?php

namespace App\Models;

use App\Events\ArticlePublished;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'date:d M Y',
        'is_paid' => 'boolean',
    ];

    protected $dispatchesEvents = [
        // We can use model events or explicit dispatch when status changes to 'published'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tickers()
    {
        return $this->belongsToMany(Ticker::class);
    }

    // Helper method to publish and dispatch event
    public function publish()
    {
        $this->update(['status' => 'published', 'published_at' => now()]);
        ArticlePublished::dispatch($this);
    }

    public function views()
    {
        return $this->hasMany(ArticleViewLog::class);
    }
}
