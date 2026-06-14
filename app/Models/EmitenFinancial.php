<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmitenFinancial extends Model
{
    protected $guarded = [];

    public function emiten()
    {
        return $this->belongsTo(Ticker::class, 'kode', 'symbol');
    }

    public function sourceDocument()
    {
        return $this->belongsTo(EmitenDocument::class, 'source_document_id');
    }
}
