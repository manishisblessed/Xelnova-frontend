<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'max_discount',
        'min_order_amount',
        'max_uses',
        'uses_count',
        'per_user_limit',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
        'per_user_limit' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active coupons
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for valid coupons (active, within date range, not exhausted)
     */
    public function scopeValid(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->active()
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereRaw('uses_count < max_uses');
            });
    }

    /**
     * Check if coupon is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can use this coupon
     */
    public function canBeUsedByUser(int $userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check per-user limit
        $userUsageCount = Order::where('user_id', $userId)
            ->where('coupon_id', $this->id)
            ->count();

        return $userUsageCount < $this->per_user_limit;
    }

    /**
     * Calculate discount for a given subtotal
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal < $this->min_order_amount) {
            return 0;
        }

        if ($this->type === 'fixed') {
            return min($this->value, $subtotal);
        }

        // Percentage discount
        $discount = ($subtotal * $this->value) / 100;

        // Apply max discount cap if set
        if ($this->max_discount) {
            $discount = min($discount, $this->max_discount);
        }

        return round($discount, 2);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('uses_count');
    }
}
