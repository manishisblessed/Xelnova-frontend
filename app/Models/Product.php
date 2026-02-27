<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'category_id',
        'brand_id',
        'seller_id',
        'price',
        'compare_at_price',
        'sku',
        'barcode',
        'hsn_code',
        'gst_rate',
        'is_inclusive_tax',
        'quantity',
        'stock_status',
        'is_active',
        'is_featured',
        'requires_shipping',
        'weight',
        'length',
        'width',
        'height',
        'is_fragile',
        'shipping_class',
        'shipping_cost',
        'is_free_shipping',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'main_image',
        'has_variants',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'highlights',
        'packaging_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'quantity' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'has_variants' => 'boolean',
        'is_inclusive_tax' => 'boolean',
        'requires_shipping' => 'boolean',
        'is_fragile' => 'boolean',
        'weight' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'is_free_shipping' => 'boolean',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'approved_at' => 'datetime',
        'highlights' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'main_image_url',
    ];

    /**
     * Get form information for CRUD operations.
     * Organized by tabs for the multi-tab form.
     *
     * @return array
     */
    public static function formInfo()
    {
        return [
            // Tab 1: Basic Info
            'name' => [
                'label' => 'Product Name',
                'type' => 'input',
                'default' => '',
                'vRule' => 'required|string|max:255',
                'tooltip' => 'Enter the product name',
                'searchable' => true,
                'sortable' => true,
                'tab' => 'basic',
            ],
            'slug' => [
                'label' => 'URL Slug',
                'type' => 'input',
                'default' => '',
                'vRule' => 'required|string|max:255|alpha_dash|unique:products,slug',
                'tooltip' => 'URL-friendly version of the name',
                'searchable' => true,
                'sortable' => true,
                'tab' => 'basic',
            ],
            'category_id' => [
                'label' => 'Category',
                'type' => 'select',
                'default' => null,
                'vRule' => 'required|exists:categories,id',
                'tooltip' => 'Select product category',
                'options' => [],
                'searchable' => false,
                'sortable' => false,
                'tab' => 'basic',
            ],
            'brand_id' => [
                'label' => 'Brand',
                'type' => 'select',
                'default' => null,
                'vRule' => 'nullable|exists:brands,id',
                'tooltip' => 'Select product brand (optional)',
                'options' => [],
                'searchable' => false,
                'sortable' => false,
                'tab' => 'basic',
            ],
            'is_active' => [
                'label' => 'Active',
                'type' => 'switch',
                'default' => true,
                'vRule' => 'sometimes|nullable|boolean',
                'tooltip' => 'Is this product active?',
                'searchable' => false,
                'sortable' => true,
                'tab' => 'basic',
            ],
            'is_featured' => [
                'label' => 'Featured',
                'type' => 'switch',
                'default' => false,
                'vRule' => 'sometimes|nullable|boolean',
                'tooltip' => 'Show this product in featured sections?',
                'searchable' => false,
                'sortable' => true,
                'tab' => 'basic',
            ],
            
            // Tab 2: Pricing & Stock
            'price' => [
                'label' => 'Price',
                'type' => 'number',
                'default' => 0,
                'vRule' => 'required|numeric|min:0',
                'tooltip' => 'Regular selling price',
                'searchable' => false,
                'sortable' => true,
                'tab' => 'pricing',
            ],
            'compare_at_price' => [
                'label' => 'Compare At Price',
                'type' => 'number',
                'default' => null,
                'vRule' => 'nullable|numeric|min:0',
                'tooltip' => 'Original price for showing discounts',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'pricing',
            ],
            'sku' => [
                'label' => 'SKU',
                'type' => 'input',
                'default' => '',
                'vRule' => 'nullable|string|max:100|unique:products,sku',
                'tooltip' => 'Stock Keeping Unit',
                'searchable' => true,
                'sortable' => true,
                'tab' => 'pricing',
            ],
            'barcode' => [
                'label' => 'Barcode',
                'type' => 'input',
                'default' => '',
                'vRule' => 'nullable|string|max:100',
                'tooltip' => 'Product barcode (ISBN, UPC, etc.)',
                'searchable' => true,
                'sortable' => false,
                'tab' => 'pricing',
            ],
            'quantity' => [
                'label' => 'Quantity',
                'type' => 'number',
                'default' => 0,
                'vRule' => 'required|integer|min:0',
                'tooltip' => 'Stock quantity available',
                'searchable' => false,
                'sortable' => true,
                'tab' => 'pricing',
            ],
            'stock_status' => [
                'label' => 'Stock Status',
                'type' => 'select',
                'default' => 'in_stock',
                'vRule' => 'required|in:in_stock,out_of_stock,backorder',
                'tooltip' => 'Current stock availability status',
                'options' => [
                    ['id' => 'in_stock', 'label' => 'In Stock'],
                    ['id' => 'out_of_stock', 'label' => 'Out of Stock'],
                    ['id' => 'backorder', 'label' => 'Available for Backorder'],
                ],
                'searchable' => false,
                'sortable' => false,
                'tab' => 'pricing',
            ],
            'hsn_code' => [
                'label' => 'HSN Code',
                'type' => 'input',
                'default' => '',
                'vRule' => 'nullable|string|max:20',
                'tooltip' => 'Harmonized System Nomenclature code for GST',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'pricing',
            ],
            'gst_rate' => [
                'label' => 'GST Rate (%)',
                'type' => 'select',
                'default' => '18',
                'vRule' => 'required|in:0,5,12,18,28',
                'tooltip' => 'GST tax rate applicable',
                'options' => [
                    ['id' => '0', 'label' => '0% (Exempt)'],
                    ['id' => '5', 'label' => '5%'],
                    ['id' => '12', 'label' => '12%'],
                    ['id' => '18', 'label' => '18%'],
                    ['id' => '28', 'label' => '28%'],
                ],
                'searchable' => false,
                'sortable' => false,
                'tab' => 'pricing',
            ],
            'is_inclusive_tax' => [
                'label' => 'Price Includes Tax',
                'type' => 'switch',
                'default' => false,
                'vRule' => 'sometimes|nullable|boolean',
                'tooltip' => 'Is the selling price inclusive of GST?',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'pricing',
            ],
            
            // Tab 3: Images (handled separately in controller)
            'main_image' => [
                'label' => 'Main Image',
                'type' => 'file',
                'default' => '',
                'vRule' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'tooltip' => 'Primary product image (max 2MB)',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'images',
            ],
            
            // Tab 4: Description
            'short_description' => [
                'label' => 'Short Description',
                'type' => 'textarea',
                'default' => '',
                'vRule' => 'nullable|string|max:500',
                'tooltip' => 'Brief product summary (shown in listings)',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'description',
            ],
            'description' => [
                'label' => 'Full Description',
                'type' => 'textarea',
                'default' => '',
                'vRule' => 'nullable|string',
                'tooltip' => 'Detailed product description',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'description',
            ],
            'highlights' => [
                'label' => 'Highlights',
                'type' => 'array',
                'default' => [],
                'vRule' => 'nullable|array',
                'tooltip' => 'Bulleted product highlights',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'description',
            ],
            
            // Tab 5: SEO
            'meta_title' => [
                'label' => 'SEO Title',
                'type' => 'input',
                'default' => '',
                'vRule' => 'nullable|string|max:255',
                'tooltip' => 'Custom page title for search engines',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'seo',
            ],
            'meta_description' => [
                'label' => 'SEO Description',
                'type' => 'textarea',
                'default' => '',
                'vRule' => 'nullable|string|max:500',
                'tooltip' => 'Meta description for search engines',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'seo',
            ],
            'meta_keywords' => [
                'label' => 'SEO Keywords',
                'type' => 'input',
                'default' => '',
                'vRule' => 'nullable|string|max:255',
                'tooltip' => 'Keywords separated by commas',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'seo',
            ],
            
            // Tab 6: Additional Info
            'requires_shipping' => [
                'label' => 'Requires Shipping',
                'type' => 'switch',
                'default' => true,
                'vRule' => 'sometimes|nullable|boolean',
                'tooltip' => 'Is this a physical product that needs shipping?',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'shipping',
            ],
            'shipping_cost' => [
                'label' => 'Shipping Cost (₹)',
                'type' => 'number',
                'default' => 0,
                'vRule' => 'nullable|numeric|min:0',
                'tooltip' => 'Shipping cost for this product',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'shipping',
            ],
            'is_free_shipping' => [
                'label' => 'Free Shipping',
                'type' => 'switch',
                'default' => false,
                'vRule' => 'sometimes|nullable|boolean',
                'tooltip' => 'Offer free shipping for this product',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'shipping',
            ],
            'weight' => [
                'label' => 'Weight (kg)',
                'type' => 'number',
                'default' => null,
                'vRule' => 'nullable|numeric|min:0',
                'tooltip' => 'Product weight in kilograms',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'shipping',
            ],
            'length' => [
                'label' => 'Length (cm)',
                'type' => 'number',
                'default' => null,
                'vRule' => 'nullable|numeric|min:0',
                'tooltip' => 'Package length in centimeters',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'shipping',
            ],
            'width' => [
                'label' => 'Width (cm)',
                'type' => 'number',
                'default' => null,
                'vRule' => 'nullable|numeric|min:0',
                'tooltip' => 'Package width in centimeters',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'shipping',
            ],
            'height' => [
                'label' => 'Height (cm)',
                'type' => 'number',
                'default' => null,
                'vRule' => 'nullable|numeric|min:0',
                'tooltip' => 'Package height in centimeters',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'shipping',
            ],
            'is_fragile' => [
                'label' => 'Fragile Item',
                'type' => 'switch',
                'default' => false,
                'vRule' => 'sometimes|nullable|boolean',
                'tooltip' => 'Does this product require careful handling?',
                'searchable' => false,
                'sortable' => false,
                'tab' => 'shipping',
            ],
            'packaging_type' => [
                'label' => 'Packaging Type',
                'type' => 'select',
                'default' => 'box',
                'vRule' => 'required|in:box,flyer',
                'tooltip' => 'Type of packaging used',
                'options' => [
                    ['id' => 'box', 'label' => 'Box'],
                    ['id' => 'flyer', 'label' => 'Flyer'],
                ],
                'searchable' => false,
                'sortable' => false,
                'tab' => 'shipping',
            ],
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate unique slug from name if not provided
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            } else {
                // Ensure provided slug is unique
                $product->slug = static::generateUniqueSlug($product->slug, null);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id);
            } elseif ($product->isDirty('slug')) {
                // Ensure updated slug is unique
                $product->slug = static::generateUniqueSlug($product->slug, $product->id);
            }
        });
    }

    /**
     * Generate a unique slug for the product
     */
    protected static function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug already exists
     */
    protected static function slugExists($slug, $ignoreId = null)
    {
        $query = static::where('slug', $slug);
        
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        
        return $query->exists();
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the category this product belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand this product belongs to.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the seller (user) who created this product.
     * Note: seller_id references users.id, not sellers.id
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the seller profile for this product's seller.
     * Use this to access business_name and other seller-specific fields.
     */
    public function sellerProfile()
    {
        return $this->hasOneThrough(
            Seller::class,
            User::class,
            'id',           // Foreign key on users table
            'user_id',      // Foreign key on sellers table
            'seller_id',    // Local key on products table
            'id'            // Local key on users table
        );
    }

    /**
     * Get the admin who approved this product.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the product gallery images.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('display_order');
    }

    /**
     * Get the product variants.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the active product variants.
     */
    public function activeVariants()
    {
        return $this->variants()->where('is_active', true);
    }

    /**
     * Get the default variant.
     */
    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    /**
     * Get the product variant options.
     */
    public function variantOptions()
    {
        return $this->hasMany(ProductVariantOption::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include approved products.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include in-stock products.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock')->where('quantity', '>', 0);
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the main image URL.
     *
     * @return string|null
     */
    public function getMainImageUrlAttribute()
    {
        if (!$this->main_image) {
            return null;
        }

        // Check if it's already an absolute URL (e.g., from Unsplash)
        if (filter_var($this->main_image, FILTER_VALIDATE_URL)) {
            return $this->main_image;
        }

        // Otherwise, it's a local storage path
        return file_url($this->main_image);
    }

    /**
     * Get the formatted status label.
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the formatted price.
     *
     * @return string
     */
    public function getPriceFormattedAttribute()
    {
        return '₹' . number_format($this->price, 2);
    }

    /**
     * Get the active status label.
     *
     * @return string
     */
    public function getActiveStatusLabelAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Check if product is on sale.
     *
     * @return bool
     */
    public function getIsOnSaleAttribute()
    {
        return $this->compare_at_price && $this->compare_at_price > $this->price;
    }

    /**
     * Get the discount percentage.
     *
     * @return int|null
     */
    public function getDiscountPercentAttribute()
    {
        if (!$this->is_on_sale) {
            return null;
        }
        return round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100);
    }

    /**
     * Get stock quantity (alias for quantity field).
     *
     * @return int
     */
    public function getStockQuantityAttribute()
    {
        return $this->quantity ?? 0;
    }

    // ==================== REVIEWS ====================

    /**
     * Get the product reviews.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get approved reviews.
     */
    public function approvedReviews()
    {
        return $this->reviews()->approved();
    }

    /**
     * Get the average rating.
     *
     * @return float
     */
    public function getAverageRatingAttribute()
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }

    /**
     * Get the reviews count.
     *
     * @return int
     */
    public function getReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

    // ==================== VARIANT HELPERS ====================

    /**
     * Get the effective price (variant-aware).
     * For products with variants, returns the lowest variant price.
     *
     * @return float
     */
    public function getEffectivePriceAttribute()
    {
        if ($this->has_variants) {
            $defaultVariant = $this->defaultVariant;
            if ($defaultVariant) {
                return $defaultVariant->price;
            }
            return $this->activeVariants()->min('price') ?? $this->price;
        }
        return $this->price;
    }

    /**
     * Get the minimum variant price (for "From ₹X" display).
     *
     * @return float|null
     */
    public function getMinVariantPriceAttribute()
    {
        if (!$this->has_variants) {
            return null;
        }
        return $this->activeVariants()->min('price');
    }

    /**
     * Get the maximum variant price.
     *
     * @return float|null
     */
    public function getMaxVariantPriceAttribute()
    {
        if (!$this->has_variants) {
            return null;
        }
        return $this->activeVariants()->max('price');
    }

    /**
     * Get the effective stock quantity (variant-aware).
     * For products with variants, returns total stock across all variants.
     *
     * @return int
     */
    public function getEffectiveStockAttribute()
    {
        if ($this->has_variants) {
            return $this->activeVariants()->sum('quantity') ?? 0;
        }
        return $this->quantity ?? 0;
    }

    /**
     * Check if product is in stock (variant-aware).
     *
     * @return bool
     */
    public function getIsInStockAttribute()
    {
        if ($this->has_variants) {
            return $this->activeVariants()->where('stock_status', '!=', 'out_of_stock')->where('quantity', '>', 0)->exists();
        }
        return $this->stock_status !== 'out_of_stock' && $this->quantity > 0;
    }

    /**
     * Get price display text.
     * Returns "₹X" for simple products, "From ₹X" for variant products.
     *
     * @return string
     */
    public function getPriceDisplayAttribute()
    {
        if ($this->has_variants && $this->min_variant_price) {
            if ($this->min_variant_price != $this->max_variant_price) {
                return 'From ₹' . number_format($this->min_variant_price, 2);
            }
            return '₹' . number_format($this->min_variant_price, 2);
        }
        return '₹' . number_format($this->price, 2);
    }
}

