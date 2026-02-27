<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantValue extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_variant_id',
        'product_variant_option_id',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the product variant.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the product variant option.
     */
    public function productVariantOption()
    {
        return $this->belongsTo(ProductVariantOption::class);
    }
}
