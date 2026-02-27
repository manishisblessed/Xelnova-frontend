<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'image',
        'description',
        'is_active',
        'featured',
        'display_order',
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
        'featured' => 'boolean',
        'display_order' => 'integer',
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
                'label' => 'Category Name',
                'type' => 'input',
                'default' => '',
                'vRule' => 'required|string|max:255',
                'tooltip' => 'Enter the category name',
                'searchable' => true,
                'sortable' => true,
            ],
            'parent_id' => [
                'label' => 'Parent Category',
                'type' => 'select',
                'default' => null,
                'vRule' => 'nullable|exists:categories,id',
                'tooltip' => 'Select parent category (leave empty for top-level category)',
                'options' => [], // Populated dynamically in controller
                'searchable' => false,
                'sortable' => false,
            ],
            'slug' => [
                'label' => 'URL Slug',
                'type' => 'input',
                'default' => '',
                'vRule' => 'required|string|max:255|alpha_dash|unique:categories,slug',
                'tooltip' => 'URL-friendly version of the name (lowercase, hyphens only)',
                'searchable' => true,
                'sortable' => true,
            ],
            'description' => [
                'label' => 'Description',
                'type' => 'textarea',
                'default' => '',
                'vRule' => 'nullable|string',
                'tooltip' => 'Brief description of the category',
                'searchable' => false,
                'sortable' => false,
            ],
            'display_order' => [
                'label' => 'Display Order',
                'type' => 'number',
                'default' => 0,
                'vRule' => 'nullable|integer|min:0',
                'tooltip' => 'Lower numbers appear first (0 = highest priority)',
                'searchable' => false,
                'sortable' => true,
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
                'tooltip' => 'Is this category active?',
                'searchable' => false,
                'sortable' => true,
            ],
            'featured' => [
                'label' => 'Featured',
                'type' => 'switch',
                'default' => false,
                'vRule' => 'sometimes|nullable|boolean',
                'tooltip' => 'Mark this category as featured',
                'searchable' => false,
                'sortable' => true,
            ],
            'image' => [
                'label' => 'Category Image',
                'type' => 'file',
                'default' => '',
                'vRule' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'tooltip' => 'Upload category image (max 2MB)',
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
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to only include top-level categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to order by display_order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the image URL.
     *
     * @return string|null
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return file_url($this->image);
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
     * Get the full category path (breadcrumb).
     *
     * @return string
     */
    public function getFullPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Get products associated with this category.
     * This relationship will be used when products module is implemented.
     */
    // public function products()
    // {
    //     return $this->hasMany(Product::class);
    // }

    /**
     * Get the variant types directly assigned to this category.
     */
    public function variantTypes()
    {
        return $this->belongsToMany(VariantType::class, 'category_variant_types')
            ->withPivot(['affects_price', 'is_required', 'display_order'])
            ->withTimestamps()
            ->orderBy('category_variant_types.display_order');
    }

    /**
     * Get the category variant type pivot records.
     */
    public function categoryVariantTypes()
    {
        return $this->hasMany(CategoryVariantType::class);
    }

    /**
     * Get all variant types including inherited from parent categories.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getAllVariantTypes()
    {
        $variantTypes = collect();
        
        // Get parent variant types first (inheritance)
        if ($this->parent) {
            $variantTypes = $this->parent->getAllVariantTypes();
        }
        
        // Merge with own variant types (own types can override parent settings)
        $ownTypes = $this->variantTypes()->get();
        
        foreach ($ownTypes as $type) {
            // Replace or add variant type
            $variantTypes = $variantTypes->filter(function ($vt) use ($type) {
                return $vt->id !== $type->id;
            })->push($type);
        }
        
        return $variantTypes->sortBy('pivot.display_order');
    }

    /**
     * Get all variant types as a flat collection for product forms.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getApplicableVariantTypes()
    {
        return $this->getAllVariantTypes()->map(function ($variantType) {
            return [
                'id' => $variantType->id,
                'name' => $variantType->name,
                'slug' => $variantType->slug,
                'input_type' => $variantType->input_type,
                'affects_price' => $variantType->pivot->affects_price ?? false,
                'is_required' => $variantType->pivot->is_required ?? false,
                'display_order' => $variantType->pivot->display_order ?? 0,
            ];
        });
    }
}
