<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppShare extends Model
{
    protected $guarded = [];

    public function shareable()
    {
        return $this->morphTo();
    }
}
