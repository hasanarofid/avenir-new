<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disclosure extends Model
{
    protected $guarded = [];

    public function ticker()
    {
        return $this->belongsTo(Ticker::class);
    }

    public function kiBrief()
    {
        return $this->hasOne(KIBrief::class);
    }
}
