<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryVariantType extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'variant_type_id',
        'affects_price',
        'is_required',
        'display_order',
    ];

    protected $casts = [
        'affects_price' => 'boolean',
        'is_required' => 'boolean',
        'display_order' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the variant type.
     */
    public function variantType()
    {
        return $this->belongsTo(VariantType::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
