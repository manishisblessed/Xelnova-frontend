<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VariantType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'input_type',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'input_type_label',
        'status_label',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($variantType) {
            if (empty($variantType->slug)) {
                $variantType->slug = Str::slug($variantType->name);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the categories that use this variant type.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_variant_types')
            ->withPivot(['affects_price', 'is_required', 'display_order'])
            ->withTimestamps();
    }

    /**
     * Get the category variant type pivot records.
     */
    public function categoryVariantTypes()
    {
        return $this->hasMany(CategoryVariantType::class);
    }

    /**
     * Get the product variant options using this type.
     */
    public function productVariantOptions()
    {
        return $this->hasMany(ProductVariantOption::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to only active variant types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the input type label.
     */
    public function getInputTypeLabelAttribute()
    {
        return match ($this->input_type) {
            'color' => 'Color Picker',
            'size' => 'Size Selector',
            'text' => 'Text Input',
            'select' => 'Dropdown Select',
            default => is_string($this->input_type) ? ucfirst($this->input_type) : '',
        };
    }

    /**
     * Get active status label.
     */
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }
}
