<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppLike extends Model
{
    protected $guarded = [];

    public function likeable()
    {
        return $this->morphTo();
    }
}
