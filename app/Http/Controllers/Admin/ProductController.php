<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class ProductController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'product',
        'resourceTitle' => 'Products',
        'iconPath' => 'M12,3L2,12H5V20H19V12H22L12,3M12,8.75A2.25,2.25 0 0,1 14.25,11A2.25,2.25 0 0,1 12,13.25A2.25,2.25 0 0,1 9.75,11A2.25,2.25 0 0,1 12,8.75Z',
        'actions' => ['c', 'r', 'u', 'd']
    ];

    public function __construct()
    {
        $this->middleware('can:product_list', ['only' => ['index', 'show']]);
        $this->middleware('can:product_create', ['only' => ['create', 'store']]);
        $this->middleware('can:product_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:product_delete', ['only' => ['destroy', 'bulkDestroy']]);
        $this->middleware('can:product_approve', ['only' => ['approve']]);
        $this->middleware('can:product_reject', ['only' => ['reject']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formInfo = Product::formInfo();
        
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) use ($formInfo) {
            $query->where(function ($query) use ($value, $formInfo) {
                Collection::wrap($value)->each(function ($value) use ($query, $formInfo) {
                    $query->orWhere('name', 'LIKE', "%{$value}%")
                          ->orWhere('sku', 'LIKE', "%{$value}%")
                          ->orWhere('barcode', 'LIKE', "%{$value}%");
                });
            });
        });

        $perPage = request()->query('perPage') ?? 10;
        $resourceData = QueryBuilder::for(Product::class)
            ->with(['category', 'brand'])
            ->defaultSort('-created_at')
            ->allowedSorts(['name', 'price', 'quantity', 'status', 'created_at', 'is_active', 'is_featured'])
            ->allowedFilters(['name', 'sku', 'status', 'is_active', 'category_id', 'brand_id', 'seller_id', $globalSearch])
            ->paginate($perPage)
            ->withQueryString();
        
        // Append computed attributes
        $resourceData->getCollection()->transform(function ($product) {
            $product->append('status_label', 'active_status_label', 'price_formatted', 'main_image_url');
            $product->category_name = $product->category?->name ?? '-';
            $product->brand_name = $product->brand?->name ?? '-';
            $product->seller_name = $product->seller?->seller?->business_name ?? $product->seller?->name ?? '-';
            return $product;
        });

        // Add bulk actions if user has permission
        if (Auth::user()->can('product_delete')) {
            $this->resourceNeo['bulkActions'] = ['bulk_delete' => []];
        }

        return Inertia::render('Admin/ProductIndexView', [
            'resourceData' => $resourceData,
            'resourceNeo' => $this->resourceNeo
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch();
            
            $table->column('main_image_url', 'Image', searchable: false, sortable: false);
            $table->column('name', 'Product Name', searchable: true, sortable: true);
            $table->column('category_name', 'Category', searchable: false, sortable: false);
            $table->column('brand_name', 'Brand', searchable: false, sortable: false);
            $table->column('seller_name', 'Seller', searchable: false, sortable: false);
            $table->column('price_formatted', 'Price', searchable: false, sortable: true);
            $table->column('quantity', 'Stock', searchable: false, sortable: true);
            $table->column('status_label', 'Status', searchable: false, sortable: true);
            $table->column('active_status_label', 'Active', searchable: false, sortable: true);
            $table->column(label: 'Actions');
            
            $table
                ->perPageOptions([10, 15, 30, 50, 100])
                ->selectFilter(key: 'status', label: 'Review Status', options: [
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->selectFilter(key: 'is_active', label: 'Active Status', options: [
                    '1' => 'Active',
                    '0' => 'Inactive',
                ])
                ->selectFilter(key: 'category_id', label: 'Category', options: $this->getCategoryOptions())
                ->selectFilter(key: 'brand_id', label: 'Brand', options: $this->getBrandOptions())
                ->selectFilter(key: 'seller_id', label: 'Seller', options: $this->getSellerOptions());
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $resourceNeo = $this->resourceNeo;
        $resourceNeo['formInfo'] = Product::formInfo();
        
        // Populate select options
        $resourceNeo['formInfo']['category_id']['options'] = $this->getCategoryOptionsForForm();
        $resourceNeo['formInfo']['brand_id']['options'] = $this->getBrandOptionsForForm();
        
        // Define tabs for multi-tab form
        $resourceNeo['tabs'] = $this->getFormTabs();
        
        // Variant Types for the frontend
        $variantTypes = \App\Models\VariantType::active()->ordered()->get();
        $categoryVariantTypes = []; // Empty for new product until category selected
        
        return Inertia::render('Admin/ProductAddEditView', compact('resourceNeo', 'variantTypes', 'categoryVariantTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $formInfo = Product::formInfo();
        $attributeNames = [];
        $validateRule = [];
        $savedArray = [];
        
        // Build validation rules and data array
        foreach (array_keys($formInfo) as $key) {
            $attributeNames[$key] = $formInfo[$key]['label'];
            if (isset($formInfo[$key]['vRule'])) {
                $validateRule[$key] = $formInfo[$key]['vRule'];
            }
        }
        
        // Extract IDs from Multiselect object format
        $validationData = $request->all();
        $validationData['category_id'] = $this->extractSelectValue($request->category_id);
        $validationData['brand_id'] = $this->extractSelectValue($request->brand_id);
        // Ensure brand_id is null if empty
        if (empty($validationData['brand_id'])) {
            $validationData['brand_id'] = null;
        }
        $validationData['stock_status'] = $this->extractSelectValue($request->stock_status);
        $validationData['gst_rate'] = $this->extractSelectValue($request->gst_rate);
        
        // Validate
        $validator = \Validator::make($validationData, $validateRule, [], $attributeNames);
        $validator->validate();
        
        // Build saved array
        foreach (array_keys($formInfo) as $key) {
            if ($key === 'main_image' && $request->hasFile('main_image')) {
                $savedArray[$key] = $request->file('main_image')->store('products/images');
            } elseif (in_array($key, ['category_id', 'brand_id', 'stock_status', 'gst_rate'])) {
                $savedArray[$key] = $validationData[$key];
            } else {
                $savedArray[$key] = $request->{$key};
            }
        }
        
        // Set highlights if present (as array, but handle empty values)
        if ($request->has('highlights')) {
            $savedArray['highlights'] = array_values(array_filter($request->highlights, fn($value) => !is_null($value) && $value !== ''));
        }
        
        // Set variant flag
        $savedArray['has_variants'] = $request->boolean('has_variants', false);
        
        // Set status to pending for review
        $savedArray['status'] = 'pending';
        
        $product = Product::create($savedArray);
        
        // Handle gallery images - check both request->all() and request->file()
        $galleryImages = [];
        
        // First, try to get from request->all() (for array format)
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^gallery_images\[(\d+)\]$/', $key, $matches)) {
                $galleryImages[(int)$matches[1]] = $value;
            }
        }
        
        // Also check request->file() for direct file uploads
        if (empty($galleryImages) && $request->hasFile('gallery_images')) {
            $galleryImages = $request->file('gallery_images');
        }
        
        
        if (!empty($galleryImages)) {
            ksort($galleryImages); // Sort by index
            foreach ($galleryImages as $index => $image) {
                if ($image instanceof \Illuminate\Http\UploadedFile) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $image->store('products/gallery'),
                        'display_order' => $index,
                    ]);
                }
            }
        }
        
        // Handle variants
        if ($savedArray['has_variants'] && $request->has('variant_options_data')) {
            $this->processVariants($request, $product);
        }

        \ActivityLog::add([
            'action' => 'created',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $product->name
        ]);

        return redirect()->route('product.index')->with([
            'message' => 'Product Created Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $formdata = $product->load('images');
        $originalCategoryId = $product->category_id; // Capture ID before modification
        
        $resourceNeo = $this->resourceNeo;
        $resourceNeo['formInfo'] = Product::formInfo();
        
        // Populate select options
        $resourceNeo['formInfo']['category_id']['options'] = $this->getCategoryOptionsForForm();
        $resourceNeo['formInfo']['brand_id']['options'] = $this->getBrandOptionsForForm($product);
        
        // Convert select values to option object format
        if ($product->category_id) {
            $category = Category::find($product->category_id);
            if ($category) {
                $formdata->category_id = ['id' => $category->id, 'label' => $category->full_path];
            }
        }
        
        if ($product->brand_id) {
            $brand = Brand::find($product->brand_id);
            if ($brand) {
                $formdata->brand_id = ['id' => $brand->id, 'label' => $brand->name];
            }
        }
        
        if ($product->stock_status) {
            $stockStatusLabels = [
                'in_stock' => 'In Stock',
                'out_of_stock' => 'Out of Stock',
                'backorder' => 'Available for Backorder',
            ];
            $formdata->stock_status = [
                'id' => $product->stock_status,
                'label' => $stockStatusLabels[$product->stock_status] ?? $product->stock_status
            ];
        }

        if ($product->gst_rate !== null) {
            $gstRates = collect($this->getGstRates())->pluck('label', 'id');
            $formdata->gst_rate = [
                'id' => (string)$product->gst_rate,
                'label' => $gstRates[(string)$product->gst_rate] ?? $product->gst_rate . '%'
            ];
        }
        
        // Add current image URLs
        if ($product->main_image) {
            $resourceNeo['formInfo']['main_image']['currentFile'] = Storage::url($product->main_image);
        }
        
        // Add gallery images data
        $formdata->gallery_images_data = $product->images->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => Storage::url($image->image_path),
                'alt_text' => $image->alt_text,
                'display_order' => $image->display_order,
            ];
        });
        
        // Define tabs for multi-tab form
        $resourceNeo['tabs'] = $this->getFormTabs();
        
        // Variant Data
        $variantTypes = \App\Models\VariantType::active()->ordered()->get();
        // Use the original captured ID, not the modified one in $product
        $categoryVariantTypes = $this->getCategoryVariantTypes($originalCategoryId);
        
        $product->load(['variantOptions.variantType', 'variants.variantValues.productVariantOption']);
        
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
                'main_image_url' => $variant->main_image ? asset('storage/' .$variant->main_image) : null,
                'option_ids' => $variant->variantValues->pluck('product_variant_option_id')->toArray(),
            ];
        });
        
        return Inertia::render('Admin/ProductAddEditView', compact(
            'formdata', 'resourceNeo', 'variantTypes', 'categoryVariantTypes', 'existingVariantOptions', 'existingVariants'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $formInfo = Product::formInfo();
        $attributeNames = [];
        $validateRule = [];
        
        foreach (array_keys($formInfo) as $key) {
            $attributeNames[$key] = $formInfo[$key]['label'];
            if (isset($formInfo[$key]['vRule'])) {
                $validateRule[$key] = $formInfo[$key]['vRule'];
            }
        }
        
        // Update unique validation rules to ignore current record
        $validateRule['slug'] = 'required|string|max:255|alpha_dash|unique:products,slug,' . $product->id;
        $validateRule['sku'] = 'nullable|string|max:100|unique:products,sku,' . $product->id;
        
        // Remove image validation if no new file is uploaded
        if (!$request->hasFile('main_image')) {
            unset($validateRule['main_image']);
        }
        
        // Extract IDs from Multiselect object format
        $validationData = $request->all();
        $validationData['category_id'] = $this->extractSelectValue($request->category_id);
        $validationData['brand_id'] = $this->extractSelectValue($request->brand_id);
        // Ensure brand_id is null if empty
        if (empty($validationData['brand_id'])) {
            $validationData['brand_id'] = null;
        }
        $validationData['stock_status'] = $this->extractSelectValue($request->stock_status);
        $validationData['gst_rate'] = $this->extractSelectValue($request->gst_rate);
        
        // Validate
        $validator = \Validator::make($validationData, $validateRule, [], $attributeNames);
        $validator->validate();
        
        // Handle file upload
        if ($request->hasFile('main_image')) {
            if ($product->main_image) {
                Storage::delete($product->main_image);
            }
            $product->main_image = $request->file('main_image')->store('products/images');
        }
        
        // Update other fields
        foreach (array_diff(array_keys($formInfo), ['main_image']) as $key) {
            if ($request->has($key)) {
                if (in_array($key, ['category_id', 'brand_id', 'stock_status', 'gst_rate'])) {
                    $product->{$key} = $validationData[$key];
                } else {
                    $product->{$key} = $request->{$key};
                }
            }
        }

        // Set highlights if present (as array, but handle empty values)
        if ($request->has('highlights')) {
            $product->highlights = array_values(array_filter($request->highlights, fn($value) => !is_null($value) && $value !== ''));
        }
        
        // Set variant flag
        $product->has_variants = $request->boolean('has_variants', false);

        $product->save();
        
        // Handle new gallery images - check both request->all() and request->file()
        $galleryImages = [];
        
        // First, try to get from request->all() (for array format)
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^gallery_images\[(\d+)\]$/', $key, $matches)) {
                $galleryImages[(int)$matches[1]] = $value;
            }
        }
        
        // Also check request->file() for direct file uploads
        if (empty($galleryImages) && $request->hasFile('gallery_images')) {
            $galleryImages = $request->file('gallery_images');
        }
        
        
        if (!empty($galleryImages)) {
            ksort($galleryImages); // Sort by index
            $maxOrder = $product->images()->max('display_order') ?? -1;
            foreach ($galleryImages as $index => $image) {
                if ($image instanceof \Illuminate\Http\UploadedFile) {

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $image->store('products/gallery'),
                        'display_order' => $maxOrder + $index + 1,
                    ]);
                }
            }
        }
        
        // Handle deleted gallery images
        $deletedImages = [];
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^deleted_gallery_images\[(\d+)\]$/', $key)) {
                $deletedImages[] = $value;
            }
        }
        
        
        if (!empty($deletedImages)) {
            $imagesToDelete = ProductImage::whereIn('id', $deletedImages)->get();
            foreach ($imagesToDelete as $image) {

                Storage::delete($image->image_path);
                $image->delete();
            }
        }
        
        // Handle variants
        if ($product->has_variants && $request->has('variant_options_data')) {
            // Check if has_variants changed from true to false is irrelevant here as it's checked above
            // If variants enabled or updated
            // Note: We might want option to keep existing if not changing?
            // For now, follow Seller logic: full replacement on update
            
            // Delete existing variants if we are re-processing them
            // But usually we only re-process if something changed.
            // Simplified: Always re-process if data is sent.
            
            $this->processVariants($request, $product, true);
        } elseif (!$product->has_variants && $product->getOriginal('has_variants')) {
            // Variants disabled, delete existing
            $product->variants()->delete();
            $product->variantOptions()->delete();
        }

        \ActivityLog::add([
            'action' => 'updated',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $product->name
        ]);

        return redirect()->route('product.index')->with([
            'message' => 'Product Updated Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Delete images
        if ($product->main_image) {
            Storage::delete($product->main_image);
        }
        foreach ($product->images as $image) {
            Storage::delete($image->image_path);
        }
        
        $productName = $product->name;
        $product->images()->delete();
        $product->delete();

        \ActivityLog::add([
            'action' => 'deleted',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $productName
        ]);

        return redirect()->route('product.index')->with([
            'message' => 'Product Deleted Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Bulk delete products.
     */
    public function bulkDestroy()
    {
        $products = Product::whereIn('id', request('ids'))->get();
        
        foreach ($products as $product) {
            if ($product->main_image) {
                Storage::delete($product->main_image);
            }
            foreach ($product->images as $image) {
                Storage::delete($image->image_path);
            }
            $product->images()->delete();
        }
        
        Product::whereIn('id', request('ids'))->delete();
        
        $uname = (count(request('ids')) > 50) ? 'Many' : implode(',', request('ids'));
        \ActivityLog::add([
            'action' => 'deleted',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $uname
        ]);
        
        return redirect()->back()->with([
            'message' => 'Selected Products Deleted Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Approve a product.
     */
    public function approve($id)
    {
        $product = Product::findOrFail($id);
        $product->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'rejection_reason' => null,
        ]);

        \ActivityLog::add([
            'action' => 'approved',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $product->name
        ]);

        return redirect()->back()->with([
            'message' => 'Product Approved Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Reject a product.
     */
    public function reject($id, Request $request)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($id);
        $product->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        \ActivityLog::add([
            'action' => 'rejected',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $product->name
        ]);

        return redirect()->back()->with([
            'message' => 'Product Rejected!',
            'msg_type' => 'warning'
        ]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Extract value from Multiselect object format.
     */
    private function extractSelectValue($value)
    {
        if (is_array($value) && isset($value['id'])) {
            return $value['id'];
        }
        return $value;
    }

    /**
     * Get category options for filter dropdown.
     */
    private function getCategoryOptions()
    {
        return Category::active()->ordered()->pluck('name', 'id')->toArray();
    }

    /**
     * Get brand options for filter dropdown.
     */
    private function getBrandOptions()
    {
        return Brand::active()->orderBy('name')->pluck('name', 'id')->toArray();
    }

    /**
     * Get category options for form select.
     */
    private function getCategoryOptionsForForm()
    {
        $categories = Category::active()->ordered()->get();
        $options = [];
        foreach ($categories as $category) {
            $options[] = [
                'id' => $category->id,
                'label' => $category->full_path
            ];
        }
        return $options;
    }

    /**
     * Get brand options for form select.
     * Shows only active brands, but includes the currently selected brand even if inactive.
     * 
     * @param Product|null $product Current product being edited (null for create)
     */
    private function getBrandOptionsForForm($product = null)
    {
        $brands = Brand::orderBy('name')->get();
        $options = [['id' => null, 'label' => '-- No Brand --']];
        
        foreach ($brands as $brand) {
            // Include brand if it's active OR if it's the currently selected brand
            $isCurrentBrand = $product && $product->brand_id === $brand->id;
            
            if ($brand->is_active || $isCurrentBrand) {
                $label = $brand->name;
                if (!$brand->is_active) {
                    $label .= ' (Inactive - currently selected)';
                }
                $options[] = [
                    'id' => $brand->id,
                    'label' => $label
                ];
            }
        }
        return $options;
    }

    /**
     * Get seller options for filter select.
     */
    private function getSellerOptions()
    {
        return \App\Models\Seller::orderBy('business_name')
            ->get()
            ->mapWithKeys(function ($seller) {
                $name = $seller->business_name ?? 'Seller #' . $seller->id;
                return [$seller->user_id => $name];
            })
            ->toArray();
    }

    /**
     * Get form tabs configuration.
     */
    private function getFormTabs()
    {
        return [
            [
                'key' => 'basic',
                'label' => 'Basic Info',
                'icon' => 'M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6M12,8A4,4 0 0,0 8,12A4,4 0 0,0 12,16A4,4 0 0,0 16,12A4,4 0 0,0 12,8Z',
            ],
            [
                'key' => 'pricing',
                'label' => 'Pricing & Stock',
                'icon' => 'M7,15H9C9,16.08 10.37,17 12,17C13.63,17 15,16.08 15,15C15,13.9 13.96,13.5 11.76,12.97C9.64,12.44 7,11.78 7,9C7,7.21 8.47,5.69 10.5,5.18V3H13.5V5.18C15.53,5.69 17,7.21 17,9H15C15,7.92 13.63,7 12,7C10.37,7 9,7.92 9,9C9,10.1 10.04,10.5 12.24,11.03C14.36,11.56 17,12.22 17,15C17,16.79 15.53,18.31 13.5,18.82V21H10.5V18.82C8.47,18.31 7,16.79 7,15Z',
            ],
            [
                'key' => 'images',
                'label' => 'Images',
                'icon' => 'M8.5,13.5L11,16.5L14.5,12L19,18H5M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19Z',
            ],
            [
                'key' => 'description',
                'label' => 'Description',
                'icon' => 'M14,17H7V15H14M17,13H7V11H17M17,9H7V7H17M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z',
            ],
            [
                'key' => 'seo',
                'label' => 'SEO Info',
                'icon' => 'M15.5,14L20.5,19L19,20.5L14,15.5V14.71L13.73,14.43C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.43,13.73L14.71,14H15.5M9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14Z',
            ],
            [
                'key' => 'shipping',
                'label' => 'Shipping',
                'icon' => 'M19,19H5V5H19M19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M13.96,12.29L11.21,15.83L9.25,13.47L6.5,17H17.5L13.96,12.29Z',
            ],
            [
                'key' => 'variants',
                'label' => 'Variants',
                'icon' => 'M17,17H7V7H17M21,1H3C1.89,1 1,1.89 1,3V21A2,2 0 0,0 3,23H21A2,2 0 0,0 23,21V3C23,1.89 22.1,1 21,1M19,21H5V5H19V21Z',
            ],
        ];
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
     * Get GST rate options.
     */
    private function getGstRates(): array
    {
        return [
            ['id' => '0', 'label' => '0% (Exempt)'],
            ['id' => '5', 'label' => '5%'],
            ['id' => '12', 'label' => '12%'],
            ['id' => '18', 'label' => '18%'],
            ['id' => '28', 'label' => '28%'],
        ];
    }

    /**
     * Process variant options and variants for a product.
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
            $option = \App\Models\ProductVariantOption::create([
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
                    ->store('products/variants', 'public');
            }
            
            $variant = \App\Models\ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variantData['sku'],
                'barcode' => $variantData['barcode'] ?? null,
                'price' => $variantData['price'],
                'compare_at_price' => $variantData['compare_at_price'] ?? null,
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
                    
                    \App\Models\ProductVariantValue::create([
                        'product_variant_id' => $variant->id,
                        'product_variant_option_id' => $realOptionId,
                    ]);
                }
            }
        }
    }
}
