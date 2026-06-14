<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmitenAIDraft extends Model
{
    protected $guarded = [];

    protected $casts = [
        'raw_json' => 'array',
        'edited_json' => 'array',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function emiten()
    {
        return $this->belongsTo(Ticker::class, 'kode', 'symbol');
    }

    public function document()
    {
        return $this->belongsTo(EmitenDocument::class, 'document_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
