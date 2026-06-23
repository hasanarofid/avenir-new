<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppComment extends Model
{
    protected $guarded = [];

    public function commentable()
    {
        return $this->morphTo();
    }
}
