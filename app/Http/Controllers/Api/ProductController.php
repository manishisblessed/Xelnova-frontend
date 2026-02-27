<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get products with filtering and pagination for infinite scroll
     */
    public function index(Request $request)
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
                $q->where('has_variants', false)
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
            $brandIds = is_array($request->brand) ? $request->brand : explode(',', $request->brand);
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
            $image = $variant->main_image_url;
            
            $variantLabel = $this->getVariantLabel($variant);
            
            $items->push([
                'id' => $variant->product_id,
                'variant_id' => $variant->id,
                'name' => $variant->product->name . ' - ' . $variantLabel,
                'variant_label' => $variantLabel,
                'slug' => $variant->product->slug,
                'image' => $image ?: 'https://via.placeholder.com/300x300?text=No+Image',
                'price' => $variant->price,
                'original_price' => $variant->compare_at_price,
                'discount' => $variant->compare_at_price && $variant->compare_at_price > $variant->price
                    ? round((($variant->compare_at_price - $variant->price) / $variant->compare_at_price) * 100)
                    : 0,
                'rating' => '4.' . rand(0, 9),
                'reviews_count' => rand(100, 2000),
                'brand' => $variant->product->brand?->name,
                'category' => $variant->product->category?->name,
                'delivery_text' => 'Free delivery by Tomorrow',
                'in_stock' => $variant->quantity > 0,
            ]);
        }
        
        foreach ($products as $product) {
            $image = $product->main_image_url;
            
            $items->push([
                'id' => $product->id,
                'variant_id' => null,
                'name' => $product->name,
                'variant_label' => null,
                'slug' => $product->slug,
                'image' => $image ?: 'https://via.placeholder.com/300x300?text=No+Image',
                'price' => $product->price,
                'original_price' => $product->compare_at_price,
                'discount' => $product->compare_at_price && $product->compare_at_price > $product->price
                    ? round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100)
                    : 0,
                'rating' => '4.' . rand(0, 9),
                'reviews_count' => rand(100, 2000),
                'brand' => $product->brand?->name,
                'category' => $product->category?->name,
                'delivery_text' => 'Free delivery by Tomorrow',
                'in_stock' => $product->quantity > 0,
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
                // Keep original order
                break;
        }
        
        // Manual pagination
        $perPage = $request->get('per_page', 24);
        $currentPage = $request->get('page', 1);
        $total = $items->count();
        $paginatedItems = $items->forPage($currentPage, $perPage)->values();
        
        return response()->json([
            'success' => true,
            'data' => $paginatedItems,
            'meta' => [
                'current_page' => (int)$currentPage,
                'last_page' => (int)ceil($total / $perPage),
                'per_page' => (int)$perPage,
                'total' => $total,
                'from' => ($currentPage - 1) * $perPage + 1,
                'to' => min($currentPage * $perPage, $total),
                'has_more' => $currentPage < ceil($total / $perPage),
            ]
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
}
