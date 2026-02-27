<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use App\Models\ProductVariantValue;
use App\Models\Category;
use App\Models\Brand;
use App\Models\VariantType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SellerProductController extends Controller
{
    /**
     * Display a listing of the seller's products.
     */
    public function index(Request $request)
    {
        $query = Product::where('seller_id', auth()->id())
            ->with([
                'category' => function ($query) {
                    $query->with('parent.parent.parent'); // Load up to 3 levels of parents
                },
                'brand'
            ]);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $products = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        
        $categories = Category::with('parent.parent.parent')->active()->ordered()->get();
        
        return view('seller.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $seller = auth()->user()->seller;
        $categories = Category::active()->ordered()->get();
        
        // Get only seller's approved brands + Generic option
        $sellerBrands = \App\Models\SellerBrand::where('seller_id', $seller->id)
            ->where('approval_status', 'approved')
            ->orderBy('brand_name')
            ->get();
        
        // Create a collection with Generic option first
        $brands = collect([
            (object)['id' => null, 'name' => 'Generic (No Brand)']
        ])->merge($sellerBrands->map(function($brand) {
            return (object)['id' => $brand->id, 'name' => $brand->brand_name];
        }));
        
        // Get all active variant types for reference (category-specific types loaded dynamically via AJAX)
        $variantTypes = VariantType::active()->ordered()->get();
        
        return view('seller.products.form', [
            'product' => null,
            'categories' => $categories,
            'brands' => $brands,
            'gstRates' => $this->getGstRates(),
            'variantTypes' => $variantTypes,
            'categoryVariantTypes' => [],
            'existingVariantOptions' => [],
            'existingVariants' => [],
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:seller_brands,id',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'stock_status' => 'required|in:in_stock,out_of_stock,backorder',
            'hsn_code' => 'nullable|string|max:20',
            'hsn_code' => 'nullable|string|max:20',
            'gst_rate' => 'required|numeric|exists:tax_rates,rate', // Check if rate exists in tax_rates table
            'is_inclusive_tax' => 'sometimes|boolean',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'is_fragile' => 'sometimes|boolean',
            'requires_shipping' => 'sometimes|boolean',
            'shipping_cost' => 'nullable|numeric|min:0',
            'is_free_shipping' => 'sometimes|boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'has_variants' => 'sometimes|boolean',
            'highlights' => 'nullable|array',
            'highlights.*' => 'nullable|string|max:255',
            'packaging_type' => 'nullable|string|in:box,flyer',
        ]);
        
        // Generate slug
        $validated['slug'] = Str::slug($validated['name']);
        
        // Set seller_id and status
        $validated['seller_id'] = auth()->id();
        $validated['status'] = 'pending'; // Pending admin approval
        
        // Handle booleans
        $validated['is_inclusive_tax'] = $request->boolean('is_inclusive_tax');
        $validated['is_fragile'] = $request->boolean('is_fragile');
        $validated['requires_shipping'] = $request->boolean('requires_shipping');
        $validated['is_free_shipping'] = $request->boolean('is_free_shipping');
        $validated['is_active'] = true;
        $validated['has_variants'] = $request->boolean('has_variants', false);
        
        // Handle highlights (filter empty values)
        if (isset($validated['highlights'])) {
            $validated['highlights'] = array_values(array_filter($validated['highlights'], fn($value) => !is_null($value) && $value !== ''));
        }
        
        // Handle main image
        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('products/images');
        }
        
        // Create product
        $product = Product::create($validated);
        
        // Handle gallery images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $image->store('products/gallery'),
                    'display_order' => $index,
                ]);
            }
        }
        
        // Handle variants if enabled
        if ($validated['has_variants'] && $request->has('variant_options_data')) {
            $this->processVariants($request, $product);
        }
        
        return redirect()->route('seller.products')
            ->with('success', 'Product created successfully. It will be visible after admin approval.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Ensure seller owns this product
        if ($product->seller_id !== auth()->id()) {
            abort(403);
        }
        
        $seller = auth()->user()->seller;
        $categories = Category::active()->ordered()->get();
        
        // Get only seller's approved brands + Generic option
        $sellerBrands = \App\Models\SellerBrand::where('seller_id', $seller->id)
            ->where('approval_status', 'approved')
            ->orderBy('brand_name')
            ->get();
        
        // Create a collection with Generic option first
        $brands = collect([
            (object)['id' => null, 'name' => 'Generic (No Brand)']
        ])->merge($sellerBrands->map(function($brand) {
            return (object)['id' => $brand->id, 'name' => $brand->brand_name];
        }));
        
        // Include current brand even if not in approved list (for existing products)
        if ($product->brand_id && !$brands->contains('id', $product->brand_id)) {
            $currentBrand = \App\Models\SellerBrand::find($product->brand_id);
            if ($currentBrand) {
                $brands->push((object)['id' => $currentBrand->id, 'name' => $currentBrand->brand_name]);
            }
        }
        
        // Load variant-related data
        $product->load(['images', 'variantOptions.variantType', 'variants.variantValues.productVariantOption']);
        
        //  Get all active variant types for reference
        $variantTypes = VariantType::active()->ordered()->get();
        
        // Get variant types for this product's category
        $categoryVariantTypes = $this->getCategoryVariantTypes($product->category_id);
        
        // Format existing variant options for frontend
        $existingVariantOptions = $product->variantOptions->groupBy('variant_type_id')->map(function($options, $typeId) {
            return $options->map(function($option) {
                return [
                    'id' => $option->id,
                    'value' => $option->value,
                    'display_value' => $option->display_value,
                    'color_code' => $option->color_code,
                ];
            })->values();
        });
        
        // Format existing variants for frontend
        $existingVariants = $product->variants->map(function($variant) {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'compare_at_price' => $variant->compare_at_price,
                'quantity' => $variant->quantity,
                'stock_status' => $variant->stock_status,
                'is_default' => $variant->is_default,
                'is_active' => $variant->is_active,
                'show_in_listing' => $variant->show_in_listing,
                'main_image_url' => $variant->main_image ? file_url($variant->main_image) : null,
                'option_ids' => $variant->variantValues->pluck('product_variant_option_id')->toArray(),
            ];
        });
        
        return view('seller.products.form', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'gstRates' => $this->getGstRates(),
            'variantTypes' => $variantTypes,
            'categoryVariantTypes' => $categoryVariantTypes,
            'existingVariantOptions' => $existingVariantOptions,
            'existingVariants' => $existingVariants,
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        // Ensure seller owns this product
        if ($product->seller_id !== auth()->id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:seller_brands,id',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'stock_status' => 'required|in:in_stock,out_of_stock,backorder',
            'hsn_code' => 'nullable|string|max:20',
            'gst_rate' => 'required|numeric|exists:tax_rates,rate', // Check if rate exists in tax_rates table
            'is_inclusive_tax' => 'sometimes|boolean',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'is_fragile' => 'sometimes|boolean',
            'requires_shipping' => 'sometimes|boolean',
            'shipping_cost' => 'nullable|numeric|min:0',
            'is_free_shipping' => 'sometimes|boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'has_variants' => 'sometimes|boolean',
            'highlights' => 'nullable|array',
            'highlights.*' => 'nullable|string|max:255',
            'packaging_type' => 'nullable|string|in:box,flyer',
        ]);
        
        // Handle booleans
        $validated['is_inclusive_tax'] = $request->boolean('is_inclusive_tax');
        $validated['is_fragile'] = $request->boolean('is_fragile');
        $validated['requires_shipping'] = $request->boolean('requires_shipping');
        $validated['is_free_shipping'] = $request->boolean('is_free_shipping');
        $validated['has_variants'] = $request->boolean('has_variants', false);
        
        // Handle highlights (filter empty values)
        if (isset($validated['highlights'])) {
            $validated['highlights'] = array_values(array_filter($validated['highlights'], fn($value) => !is_null($value) && $value !== ''));
        }
        
        // Handle main image
        if ($request->hasFile('main_image')) {
            // Delete old image
            if ($product->main_image) {
                Storage::delete($product->main_image);
            }
            $validated['main_image'] = $request->file('main_image')->store('products/images');
        }
        
        // Update product
        $product->update($validated);
        
        // Handle deleted gallery images
        if ($request->filled('deleted_images')) {
            $deletedIds = explode(',', $request->deleted_images);
            $imagesToDelete = ProductImage::whereIn('id', $deletedIds)
                ->where('product_id', $product->id)
                ->get();
            
            foreach ($imagesToDelete as $image) {
                Storage::delete($image->image_path);
                $image->delete();
            }
        }
        
        // Handle new gallery images
        if ($request->hasFile('gallery_images')) {
            $maxOrder = $product->images()->max('display_order') ?? -1;
            foreach ($request->file('gallery_images') as $index => $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $image->store('products/gallery'),
                    'display_order' => $maxOrder + $index + 1,
                ]);
            }
        }
        
        // Handle variants if enabled
        if ($validated['has_variants'] && $request->has('variant_options_data')) {
            // Delete existing variants if has_variants changed from true to false
            if (!$validated['has_variants'] && $product->has_variants) {
                $product->variants()->delete();
                $product->variantOptions()->delete();
            } else {
                $this->processVariants($request, $product, true);
            }
        } elseif (!$validated['has_variants'] && $product->has_variants) {
            // Variants disabled, delete existing
            $product->variants()->delete();
            $product->variantOptions()->delete();
        }
        
        return redirect()->route('seller.products')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        // Ensure seller owns this product
        if ($product->seller_id !== auth()->id()) {
            abort(403);
        }
        
        // Soft delete
        $product->delete();
        
        return redirect()->route('seller.products')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Get GST rate options.
     */
    private function getGstRates(): array
    {
        return \App\Models\TaxRate::active()
            ->orderBy('rate')
            ->get()
            ->map(function ($rate) {
                return [
                    'id' => (string) $rate->rate, // Using rate as ID to maintain compatibility with existing logic storing rate directly
                    'label' => $rate->name
                ];
            })
            ->toArray();
    }

    /**
     * Get variant types assigned to a category.
     */
    private function getCategoryVariantTypes($categoryId)
    {
        if (!$categoryId) {
            return [];
        }
        
        $category = Category::with('variantTypes')->find($categoryId);
        if (!$category) {
            return [];
        }
        
        return $category->variantTypes->map(function($vt) {
            return [
                'id' => $vt->id,
                'name' => $vt->name,
                'slug' => $vt->slug,
                'input_type' => $vt->input_type,
            ];
        })->toArray();
    }

    /**
     * Process variant options and variants for a product.
     * 
     * @param Request $request
     * @param Product $product
     * @param bool $isUpdate
     */
    private function processVariants(Request $request, Product $product, $isUpdate = false)
    {
        // If updating, clear existing variants and options
        if ($isUpdate) {
            $product->variants()->delete();
            $product->variantOptions()->delete();
        }
        
        // Decode variant options data (comes as JSON from frontend)
        $variantOptionsData = json_decode($request->input('variant_options_data'), true);
        $variantsData = json_decode($request->input('variants_data'), true);
        
        if (empty($variantOptionsData) || empty($variantsData)) {
            return;
        }
        
        // Step 1: Save variant options and  build ID mapping
        $optionIdMap = []; // Maps temp IDs to real database IDs
        
        foreach ($variantOptionsData as $variantOption) {
            $option = ProductVariantOption::create([
                'product_id' => $product->id,
                'variant_type_id' => $variantOption['variant_type_id'],
                'value' => $variantOption['value'],
                'display_value' => $variantOption['display_value'] ?? $variantOption['value'],
                'color_code' => $variantOption['color_code'] ?? null,
                'display_order' => $variantOption['display_order'] ?? 0,
            ]);
            
            // Map temp ID to real ID
            if (isset($variantOption['temp_id'])) {
                $optionIdMap[$variantOption['temp_id']] = $option->id;
            }
        }
        
        // Step 2: Save variants with their option links
        foreach ($variantsData as $index => $variantData) {
            // Handle variant image upload if present
            $variantImagePath = null;
            if ($request->hasFile("variant_image_{$index}")) {
                $variantImagePath = $request->file("variant_image_{$index}")
                    ->store('products/variants');
            }
            
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variantData['sku'],
                'barcode' => $variantData['barcode'] ?? null,
                'price' => $variantData['price'],
                'compare_at_price' => $variantData['compare_at_price'] ??null,
                'quantity' => $variantData['quantity'],
                'stock_status' => $variantData['stock_status'] ?? 'in_stock',
                'main_image' => $variantImagePath,
                'is_default' => $variantData['is_default'] ?? false,
                'is_active' => $variantData['is_active'] ?? true,
                'show_in_listing' => $variantData['show_in_listing'] ?? false,
            ]);
            
            // Step 3: Link variant to its options
            if (isset($variantData['option_ids']) && is_array($variantData['option_ids'])) {
                foreach ($variantData['option_ids'] as $tempOrRealId) {
                    // Map temp ID to real ID if needed
                    $realOptionId = $optionIdMap[$tempOrRealId] ?? $tempOrRealId;
                    
                    ProductVariantValue::create([
                        'product_variant_id' => $variant->id,
                        'product_variant_option_id' => $realOptionId,
                    ]);
                }
            }
        }
    }
}
