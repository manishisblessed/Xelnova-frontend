<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'sub_order_id',
        'product_id',
        'variant_id',
        'variant_details',
        'seller_id',
        'product_name',
        'product_image',
        'product_options',
        'quantity',
        'price',
        'total',
        'status',
        'tracking_number',
        'courier',
        // New tax and shipping fields
        'tax_amount',
        'tax_rate',
        'shipping_cost',
        'is_free_shipping',
        'is_inclusive_tax',
    ];

    protected $casts = [
        'product_options' => 'array',
        'variant_details' => 'array',
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'is_free_shipping' => 'boolean',
        'is_inclusive_tax' => 'boolean',
    ];

    /**
     * Get the order this item belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the sub-order this item belongs to
     */
    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the seller (user)
     * Note: seller_id references users.id, not sellers.id
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the seller profile
     * Use this to access business_name and other seller-specific fields.
     */
    public function sellerProfile()
    {
        return $this->hasOneThrough(
            Seller::class,
            User::class,
            'id',           // Foreign key on users table
            'user_id',      // Foreign key on sellers table
            'seller_id',    // Local key on order_items table
            'id'            // Local key on users table
        );
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'packed' => 'bg-indigo-100 text-indigo-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'out_for_delivery' => 'bg-indigo-100 text-indigo-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'returned' => 'bg-gray-100 text-gray-800',
            'refunded' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get product image URL
     */
    public function getImageUrlAttribute(): string
    {
        if (!$this->product_image) {
            return asset('images/placeholder-product.png');
        }

        if (str_starts_with($this->product_image, 'http')) {
            return $this->product_image;
        }

        return asset('storage/' . $this->product_image);
    }
}
