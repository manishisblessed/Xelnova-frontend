<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get form information for CRUD operations.
     *
     * @return array
     */
    public static function formInfo()
    {
        return [
            'name' => [
                'label' => 'Brand Name',
                'type' => 'input',
                'default' => '',
                'vRule' => 'required|string|max:255|unique:brands,name',
                'tooltip' => 'Enter the brand name',
                'searchable' => true,
                'sortable' => true,
            ],
            'slug' => [
                'label' => 'URL Slug',
                'type' => 'input',
                'default' => '',
                'vRule' => 'required|string|max:255|alpha_dash|unique:brands,slug',
                'tooltip' => 'URL-friendly version of the name (lowercase, hyphens only)',
                'searchable' => true,
                'sortable' => true,
            ],
            'description' => [
                'label' => 'Description',
                'type' => 'textarea',
                'default' => '',
                'vRule' => 'nullable|string',
                'tooltip' => 'Brief description of the brand',
                'searchable' => false,
                'sortable' => false,
            ],
            'meta_title' => [
                'label' => 'SEO Title',
                'type' => 'input',
                'default' => '',
                'vRule' => 'nullable|string|max:255',
                'tooltip' => 'SEO meta title (optional)',
                'searchable' => false,
                'sortable' => false,
            ],
            'meta_description' => [
                'label' => 'SEO Description',
                'type' => 'textarea',
                'default' => '',
                'vRule' => 'nullable|string|max:500',
                'tooltip' => 'SEO meta description (optional)',
                'searchable' => false,
                'sortable' => false,
            ],
            'is_active' => [
                'label' => 'Active Status',
                'type' => 'switch',
                'default' => true,
                'vRule' => 'sometimes|nullable|boolean',
                'tooltip' => 'Is this brand active?',
                'searchable' => false,
                'sortable' => true,
            ],
            'logo' => [
                'label' => 'Brand Logo',
                'type' => 'file',
                'default' => '',
                'vRule' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'tooltip' => 'Upload brand logo image (max 2MB)',
                'searchable' => false,
                'sortable' => false,
            ],
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name if not provided
        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });

        static::updating(function ($brand) {
            if ($brand->isDirty('name') && empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    /**
     * Scope a query to only include active brands.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the logo URL.
     *
     * @return string|null
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null;
        }

        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        return file_url($this->logo);
    }

    /**
     * Get the formatted status label.
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get products associated with this brand.
     * This relationship will be used when products module is implemented.
     */
    // public function products()
    // {
    //     return $this->hasMany(Product::class);
    // }
}
