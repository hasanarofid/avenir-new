<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partner extends Model
{
    protected $fillable = [
        'user_id',
        'certification',
        'specializations',
        'portfolio_link',
        'portfolio_pdf',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'is_verified',
    ];

    protected $casts = [
        'specializations' => 'array',
        'is_verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
