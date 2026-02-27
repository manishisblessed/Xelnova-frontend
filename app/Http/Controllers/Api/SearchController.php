<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search autocomplete
     */
    public function autocomplete(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => [],
                    'categories' => [],
                    'brands' => [],
                ],
            ]);
        }

        // Search products
        $products = Product::where('is_active', true)
            ->where('status', 'approved')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'price', 'main_image')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->formatted_price,
                    'image' => $product->main_image_url,
                    'url' => route('marketplace.product.detail', $product->id),
                ];
            });

        // Search categories
        $categories = Category::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->select('id', 'name', 'slug')
            ->limit(3)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'url' => route('marketplace.products', ['category' => $category->slug]),
                ];
            });

        // Search brands
        $brands = Brand::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->select('id', 'name', 'slug')
            ->limit(3)
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'url' => route('marketplace.products', ['brand' => $brand->id]),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'categories' => $categories,
                'brands' => $brands,
            ],
        ]);
    }

    /**
     * Advanced search
     */
    public function search(Request $request)
    {
        $query = Product::with(['category', 'brand', 'seller'])
            ->where('is_active', true)
            ->where('status', 'approved');

        // Text search
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Brand filter
        if ($request->filled('brand')) {
            $query->whereIn('brand_id', (array)$request->brand);
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Rating filter
        if ($request->filled('min_rating')) {
            $query->whereHas('approvedReviews', function ($q) use ($request) {
                $q->havingRaw('AVG(rating) >= ?', [$request->min_rating]);
            });
        }

        // In stock only
        if ($request->boolean('in_stock')) {
            $query->where('stock_status', 'in_stock');
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->withAvg('approvedReviews', 'rating')
                      ->orderByDesc('approved_reviews_avg_rating');
                break;
            case 'popular':
                $query->withCount('reviews')
                      ->orderByDesc('reviews_count');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(24);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get popular searches
     */
    public function popularSearches()
    {
        // For now, return static popular searches
        // In production, this would come from analytics
        $searches = [
            'Laptops',
            'Mobile Phones',
            'Headphones',
            'Smart Watch',
            'Camera',
            'Gaming Console',
            'Bluetooth Speaker',
            'Power Bank',
        ];

        return response()->json([
            'success' => true,
            'data' => $searches,
        ]);
    }
}
