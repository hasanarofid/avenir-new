<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleViewLog extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'article_id',
        'user_id',
        'ip_address',
        'created_at'
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
