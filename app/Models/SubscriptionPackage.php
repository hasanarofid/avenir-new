<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'price', 'period_text', 'per_month_text', 'save_text',
        'duration_days', 'badge', 'special_bg', 'image_path',
        'discount_percent', 'discount_end_at', 'is_active'
    ];

    protected $casts = [
        'special_bg' => 'boolean',
        'is_active' => 'boolean',
        'discount_end_at' => 'datetime',
    ];

    /**
     * Get the active discounted price if applicable.
     */
    public function getActivePriceAttribute()
    {
        if ($this->discount_percent > 0 && $this->discount_end_at && $this->discount_end_at->isFuture()) {
            return (int) round($this->price * (1 - ($this->discount_percent / 100)));
        }
        return $this->price;
    }

    /**
     * Check if the package has an active discount.
     */
    public function getHasActiveDiscountAttribute()
    {
        return $this->discount_percent > 0 && $this->discount_end_at && $this->discount_end_at->isFuture();
    }
}
