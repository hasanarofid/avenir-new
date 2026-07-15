<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnershipManualInput extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticker',
        'ubo_image_path',
        'shareholder_image_path'
    ];
}
