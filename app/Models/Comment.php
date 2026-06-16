<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'research_comments';

    protected $fillable = [
        'research_id',
        'user_id',
        'guest_name',
        'guest_ip',
        'content',
    ];

    public function research()
    {
        return $this->belongsTo(Research::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
