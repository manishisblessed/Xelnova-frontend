<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_type_id',
        'value',
        'display_value',
        'color_code',
        'image_path',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the product this option belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant type.
     */
    public function variantType()
    {
        return $this->belongsTo(VariantType::class);
    }

    /**
     * Get the product variants that use this option.
     */
    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_values');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        return asset('storage/' . $this->image_path);
    }
}
