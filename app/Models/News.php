<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'date',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tickers()
    {
        return $this->belongsToMany(Ticker::class, 'news_ticker');
    }
}
