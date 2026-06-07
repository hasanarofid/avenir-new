<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AILog extends Model
{
    protected $table = 'ai_logs';

    protected $fillable = [
        'feature',
        'input_hash',
        'output',
        'model',
        'sources',
        'reviewer_id',
    ];

    protected $casts = [
        'sources' => 'array',
    ];
}
