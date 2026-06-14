<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmitenDocument extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_audited' => 'boolean',
    ];

    public function emiten()
    {
        return $this->belongsTo(Ticker::class, 'kode', 'symbol');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
