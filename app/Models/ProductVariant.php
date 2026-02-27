<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'price',
        'compare_at_price',
        'quantity',
        'stock_status',
        'main_image',
        'is_default',
        'is_active',
        'show_in_listing',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'quantity' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'show_in_listing' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'main_image_url',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the product this variant belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant options for this variant.
     */
    public function options()
    {
        return $this->belongsToMany(ProductVariantOption::class, 'product_variant_values');
    }

    /**
     * Get the variant values (junction records).
     */
    public function variantValues()
    {
        return $this->hasMany(ProductVariantValue::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to only active variants.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to only in-stock variants.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock')->where('quantity', '>', 0);
    }

    /**
     * Scope to get the default variant.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the main image URL.
     */
    public function getMainImageUrlAttribute()
    {
        if (!$this->main_image) {
            // Fall back to product image
            return $this->product?->main_image_url;
        }

        if (filter_var($this->main_image, FILTER_VALIDATE_URL)) {
            return $this->main_image;
        }

        return file_url($this->main_image);
    }

    /**
     * Get the formatted price.
     */
    public function getPriceFormattedAttribute()
    {
        return '₹' . number_format($this->price, 2);
    }

    /**
     * Check if variant is on sale.
     */
    public function getIsOnSaleAttribute()
    {
        return $this->compare_at_price && $this->compare_at_price > $this->price;
    }

    /**
     * Get the discount percentage.
     */
    public function getDiscountPercentAttribute()
    {
        if (!$this->is_on_sale) {
            return null;
        }
        return round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
    }

    /**
     * Get the variant name composed of option values.
     */
    public function getVariantNameAttribute()
    {
        return $this->options->pluck('display_value')->implode(' / ');
    }

    /**
     * Check if variant is available (active and in stock).
     */
    public function getIsAvailableAttribute()
    {
        return $this->is_active && $this->stock_status !== 'out_of_stock' && $this->quantity > 0;
    }
}
