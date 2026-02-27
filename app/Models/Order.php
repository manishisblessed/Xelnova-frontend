<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'shipping_address',
        'billing_address',
        'subtotal',
        'discount',
        'shipping_charge',
        'tax',
        'total',
        'payment_method',
        'payment_id',
        'payment_status',
        'order_status',
        'coupon_id',
        'coupon_code',
        'coupon_discount',
        'notes',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'XN';
        $date = now()->format('ymd');
        $random = strtoupper(Str::random(4));
        $sequence = str_pad((self::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$date}{$sequence}{$random}";
    }

    /**
     * Get the user that placed the order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the coupon used
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get sub-orders (seller-wise splits)
     */
    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    /**
     * Calculate aggregated status from sub-orders
     */
    public function getAggregatedStatusAttribute(): string
    {
        if ($this->subOrders->isEmpty()) {
            return $this->order_status;
        }

        $statuses = $this->subOrders->pluck('status')->unique();
        
        // All same status
        if ($statuses->count() === 1) {
            return $statuses->first();
        }
        
        // Check for delivery status
        if ($statuses->contains('delivered')) {
            if ($statuses->every(fn($s) => $s === 'delivered')) {
                return 'delivered';
            }
            return 'partial_delivered';
        }
        
        // Check for shipped status
        if ($statuses->every(fn($s) => in_array($s, ['shipped', 'out_for_delivery', 'delivered']))) {
            return 'shipped';
        }
        
        // Check for cancelled
        if ($statuses->every(fn($s) => $s === 'cancelled')) {
            return 'cancelled';
        }
        
        // Default to processing
        return 'processing';
    }

    /**
     * Get shipping address as formatted string
     */
    public function getFormattedShippingAddressAttribute(): string
    {
        $addr = $this->shipping_address;
        if (!$addr) {
            return '';
        }

        $parts = array_filter([
            $addr['name'] ?? null,
            $addr['address_line_1'] ?? null,
            $addr['address_line_2'] ?? null,
            $addr['landmark'] ?? null,
            $addr['city'] ?? null,
            ($addr['state'] ?? '') . ' - ' . ($addr['pincode'] ?? ''),
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get order status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->order_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'out_for_delivery' => 'bg-indigo-100 text-indigo-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'returned' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get payment status badge class
     */
    public function getPaymentBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->order_status, ['pending', 'confirmed', 'processing']);
    }

    /**
     * Mark order as confirmed
     */
    public function confirm(): void
    {
        $this->update([
            'order_status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Get total items count
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }
}
