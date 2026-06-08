<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticker extends Model
{
    protected $guarded = [];

    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }

    public function disclosures()
    {
        return $this->hasMany(Disclosure::class);
    }
}
