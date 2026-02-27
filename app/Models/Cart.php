<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'coupon_id',
    ];

    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the coupon applied to the cart
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the items in the cart
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get subtotal (sum of all items)
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Get discount amount
     */
    public function getDiscountAttribute(): float
    {
        if (!$this->coupon) {
            return 0;
        }

        return $this->coupon->calculateDiscount($this->subtotal);
    }

    /**
     * Get total after discount
     */
    public function getTotalAttribute(): float
    {
        return max(0, $this->subtotal - $this->discount);
    }

    /**
     * Get total items count
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get total unique products count
     */
    public function getProductsCountAttribute(): int
    {
        return $this->items->count();
    }
}
