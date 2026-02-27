<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Page;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Homepage
     */
    public function home()
    {
        // Load top 6 featured categories ordered by display_order
        $categories = Category::with('parent.parent.parent')
            ->active()
            ->featured()
            ->ordered()
            ->limit(6)
            ->get();
        
        // Load featured products (marked as is_featured = true)
        $featuredProducts = Product::with([
                'category' => function($q) { $q->with('parent.parent'); },
                'brand',
                'seller'
            ])
            ->where('is_featured', true)
            ->where('is_active', true)
            ->where('status', 'approved')
            ->inRandomOrder()
            ->limit(12)
            ->get();
        
        // Load flash deal products (products with high discount)
        $flashDeals = Product::with([
                'category' => function($q) { $q->with('parent.parent'); },
                'brand',
                'seller'
            ])
            ->where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('compare_at_price')
            ->whereRaw('((compare_at_price - price) / compare_at_price * 100) >= 30') // 30%+ discount
            ->inRandomOrder()
            ->limit(6)
            ->get();
        
        return view('marketplace.index', compact('categories', 'featuredProducts', 'flashDeals'));
    }

    /**
     * Product listing page
     */
    public function products(Request $request)
    {
        // Query 1: Variants that should be listed separately
        $variantsQuery = ProductVariant::with([
                'product.category' => function($q) { $q->with('parent.parent'); },
                'product.brand',
                'product.seller',
                'variantValues.productVariantOption.variantType'
            ])
            ->where('is_active', true)
            ->where('show_in_listing', true)
            ->whereHas('product', function($q) {
                $q->where('is_active', true)
                  ->where('status', 'approved');
            });
        
        // Query 2: Products without variants OR products with variants but none listed separately
        $productsQuery = Product::with([
                'category' => function($q) { $q->with('parent.parent'); },
                'brand',
                'seller'
            ])
            ->where('is_active', true)
            ->where('status', 'approved')
            ->where(function($q) {
                // Products without variants
                $q->where('has_variants', false)
                  // OR products with variants but no variants marked for listing
                  ->orWhereDoesntHave('variants', function($vq) {
                      $vq->where('show_in_listing', true);
                  });
            });
        
        // Apply filters to both queries
        
        // Category filter
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $variantsQuery->whereHas('product', fn($q) => $q->where('category_id', $category->id));
                $productsQuery->where('category_id', $category->id);
            }
        }
        
        // Brand filter
        if ($request->filled('brand')) {
            $brandIds = (array)$request->brand;
            $variantsQuery->whereHas('product', fn($q) => $q->whereIn('brand_id', $brandIds));
            $productsQuery->whereIn('brand_id', $brandIds);
        }
        
        // Price range filter
        if ($request->filled('min_price')) {
            $variantsQuery->where('price', '>=', $request->min_price);
            $productsQuery->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $variantsQuery->where('price', '<=', $request->max_price);
            $productsQuery->where('price', '<=', $request->max_price);
        }
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $variantsQuery->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
            $productsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }
        
        // Get results
        $variants = $variantsQuery->get();
        $products = $productsQuery->get();
        
        // Format variants as listing items
        $items = collect();
        
        foreach ($variants as $variant) {
            $variantLabel = $this->getVariantLabel($variant);
            $items->push([
                'type' => 'variant',
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'name' => $variant->product->name . ' - ' . $variantLabel,
                'variant_label' => $variantLabel,
                'slug' => $variant->product->slug,
                'price' => $variant->price,
                'compare_at_price' => $variant->compare_at_price,
                'image' => $variant->main_image_url ?? $variant->product->main_image_url,
                'category' => $variant->product->category,
                'brand' => $variant->product->brand,
                'quantity' => $variant->quantity,
                'rating' => $variant->product->average_rating ?? 0,
                'reviews_count' => $variant->product->reviews_count ?? 0,
            ]);
        }
        
        foreach ($products as $product) {
            $items->push([
                'type' => 'product',
                'product_id' => $product->id,
                'variant_id' => null,
                'name' => $product->name,
                'variant_label' => null,
                'slug' => $product->slug,
                'price' => $product->price,
                'compare_at_price' => $product->compare_at_price,
                'image' => $product->main_image_url,
                'category' => $product->category,
                'brand' => $product->brand,
                'quantity' => $product->quantity,
                'rating' => $product->average_rating ?? 0,
                'reviews_count' => $product->reviews_count ?? 0,
            ]);
        }
        
        // Sort combined items
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $items = $items->sortBy('price')->values();
                break;
            case 'price_high':
                $items = $items->sortByDesc('price')->values();
                break;
            default:
                // Keep original order (latest first from queries)
                break;
        }
        
        // Paginate manually
        $perPage = 24;
        $currentPage = $request->get('page', 1);
        $productsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($currentPage, $perPage),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Load categories and brands for filters
        $categories = Category::with('parent.parent.parent')
            ->active()
            ->ordered()
            ->get();
        $brands = Brand::active()->orderBy('name')->get();
        
        return view('marketplace.products', [
            'products' => $productsPaginated,
            'categories' => $categories,
            'brands' => $brands
        ]);
    }
    
    /**
     * Get variant label from variant values.
     */
    private function getVariantLabel($variant)
    {
        return $variant->variantValues
            ->map(fn($vv) => $vv->productVariantOption->display_value)
            ->join(' / ');
    }

    /**
     * Product detail page
     */
    public function productDetail($slug, Request $request)
    {
        $product = Product::with([
                'category' => function($q) { $q->with('parent.parent'); },
                'brand',
                'seller',
                'images',
                'variantOptions.variantType',
                'variants' => function($q) {
                    $q->where('is_active', true)
                      ->with('variantValues.productVariantOption.variantType');
                }
            ])
            ->where('is_active', true)
            ->where('status', 'approved')
            ->where('slug', $slug)
            ->firstOrFail();
        
        // Prepare variant data for frontend
        $variantTypes = [];
        $variants = [];
        $defaultVariant = null;
        
        if ($product->has_variants && $product->variants->count() > 0) {
            // Group variant options by variant type
            $optionsByType = [];
            foreach ($product->variantOptions as $option) {
                if (!isset($optionsByType[$option->variant_type_id])) {
                    $optionsByType[$option->variant_type_id] = [
                        'id' => $option->variantType->id,
                        'name' => $option->variantType->name,
                        'input_type' => $option->variantType->input_type,
                        'options' => []
                    ];
                }
                $optionsByType[$option->variant_type_id]['options'][] = [
                    'id' => $option->id,
                    'value' => $option->value,
                    'display_value' => $option->display_value,
                    'color_code' => $option->color_code,
                ];
            }
            // Sort the variant types by Category display_order if available
            $categoryVariantTypes = $product->category->getAllVariantTypes();
            $sortedTypeIds = $categoryVariantTypes->pluck('id')->toArray();
            
            // Reorder $optionsByType based on $sortedTypeIds
            $sortedOptions = [];
            
            // First add types found in Category order
            foreach ($sortedTypeIds as $typeId) {
                if (isset($optionsByType[$typeId])) {
                    $sortedOptions[] = $optionsByType[$typeId];
                    unset($optionsByType[$typeId]);
                }
            }
            
            // Then add any remaining types (that might not be in category pivot for some reason)
            foreach ($optionsByType as $remaining) {
                $sortedOptions[] = $remaining;
            }
            
            $variantTypes = $sortedOptions;
            
            // Format variants for frontend
            foreach ($product->variants as $variant) {
                $optionIds = $variant->variantValues->pluck('product_variant_option_id')->toArray();
                $variants[] = [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'compare_at_price' => $variant->compare_at_price,
                    'quantity' => $variant->quantity,
                    'stock_status' => $variant->stock_status,
                    'option_ids' => $optionIds,
                    'is_default' => $variant->is_default,
                    'is_active' => $variant->is_active,
                    'main_image_url' => $variant->main_image_url,
                ];
            }
            
            // Check if variant_id is provided in URL (from product listing)
            $requestedVariantId = $request->get('variant_id');
            if ($requestedVariantId && count($variants) > 0) {
                $requestedVariant = collect($variants)->firstWhere('id', (int)$requestedVariantId);
                if ($requestedVariant) {
                    $defaultVariant = $requestedVariant;
                } else {
                    // Requested variant not found, proceed with normal logic
                    $defaultVariant = null;
                }
            }
            
            // If no variant requested or not found, find default variant or first active variant with stock
            if (!isset($defaultVariant)) {
                $defaultVariant = collect($variants)->first(function($v) {
                    return $v['is_default'] && $v['is_active'] && $v['quantity'] > 0;
                });
                
                // If default variant is out of stock, find first in-stock variant
                if (!$defaultVariant) {
                    $defaultVariant = collect($variants)->first(function($v) {
                        return $v['is_active'] && $v['quantity'] > 0;
                    });
                }
                
                // If no in-stock variant, use default or first variant
                if (!$defaultVariant) {
                    $defaultVariant = collect($variants)->firstWhere('is_default', true) 
                                   ?? collect($variants)->firstWhere('is_active', true)
                                   ?? $variants[0] ?? null;
                }
            }
        }
        
        // Load related products from same category
        $relatedProducts = Product::with(['category', 'brand'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->where('status', 'approved')
            ->inRandomOrder()
            ->limit(6)
            ->get();
        
        // Check for user's default pincode if logged in
        $userPincode = null;
        if (auth()->check()) {
            $defaultAddress = auth()->user()->addresses()->where('is_default', true)->first();
            if ($defaultAddress) {
                // Address model uses 'pincode', not 'postal_code'
                $userPincode = $defaultAddress->pincode;
            }
        }

        return view('marketplace.product-detail', compact('product', 'relatedProducts', 'variantTypes', 'variants', 'defaultVariant', 'userPincode'));
    }

    /**
     * Shopping cart page
     */
    public function cart()
    {
        return view('marketplace.cart');
    }

    /**
     * Checkout page
     */
    public function checkout()
    {
        return view('marketplace.checkout');
    }

    /**
     * Customer login page
     */
    public function login()
    {
        return view('marketplace.auth.login');
    }

    /**
     * Customer registration page
     */
    public function register()
    {
        return view('marketplace.auth.register');
    }

    /**
     * My orders page
     */
    public function myOrders()
    {
        if (!auth()->check()) {
            return redirect()->route('customer.login')->with('redirect', route('account.orders'));
        }

        $orders = auth()->user()->orders()
            ->with(['items.product', 'items.seller'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('marketplace.account.my-orders', compact('orders'));
    }

    /**
     * Order detail page
     */
    public function orderDetail($orderNumber)
    {
        if (!auth()->check()) {
            return redirect()->route('customer.login');
        }

        $order = auth()->user()->orders()
            ->where('order_number', $orderNumber)
            ->with([
                'items.product', 
                'items.seller',
                'subOrders.seller',
                'subOrders.items'
            ])
            ->firstOrFail();

        return view('marketplace.account.order-detail', compact('order'));
    }

    /**
     * Order tracking page
     */
    public function orderTracking(Request $request)
    {
        $order = null;
        
        if ($request->filled('order_number')) {
            $orderNumber = $request->order_number;
            
            if (auth()->check()) {
                $order = auth()->user()->orders()
                    ->where('order_number', $orderNumber)
                    ->with(['items.product'])
                    ->first();
            }
        }

        return view('marketplace.account.order-tracking', compact('order'));
    }

    /**
     * Profile page
     */
    public function profile()
    {
        if (!auth()->check()) {
            return redirect()->route('customer.login')->with('redirect', route('account.profile'));
        }

        $user = auth()->user();
        $addresses = $user->addresses()->orderBy('is_default', 'desc')->get();

        return view('marketplace.account.profile', compact('user', 'addresses'));
    }

    /**
     * Wishlist page
     */
    public function wishlist()
    {
        if (!auth()->check()) {
            return redirect()->route('customer.login')->with('redirect', route('account.wishlist'));
        }

        $wishlistItems = auth()->user()->wishlistItems()
            ->with(['product.category', 'product.brand'])
            ->latest()
            ->get();

        return view('marketplace.account.wishlist', compact('wishlistItems'));
    }

    /**
     * About page
     */
    public function about()
    {
        return $this->renderPageBySlug('about-us');
    }

    /**
     * Contact page
     */
    public function contact()
    {
        return $this->renderPageBySlug('contact-us');
    }

    /**
     * FAQ page
     */
    public function faq()
    {
        return $this->renderPageBySlug('faq');
    }

    /**
     * Terms page
     */
    public function terms()
    {
        return $this->renderPageBySlug('terms-conditions');
    }

    /**
     * Privacy page
     */
    public function privacy()
    {
        return $this->renderPageBySlug('privacy-policy');
    }

    /**
     * Returns policy page
     */
    public function returns()
    {
        return $this->renderPageBySlug('return-policy');
    }

    /**
     * Dynamic page by slug.
     */
    public function page(string $slug)
    {
        return $this->renderPageBySlug($slug);
    }

    /**
     * Render an active CMS page by slug.
     */
    protected function renderPageBySlug(string $slug)
    {
        $page = Page::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('marketplace.pages.show', compact('page'));
    }
}
