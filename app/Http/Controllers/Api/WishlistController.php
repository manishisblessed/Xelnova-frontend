<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Get all wishlist items for the authenticated user
     */
    public function index()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to view wishlist',
            ], 401);
        }

        $wishlistItems = Auth::user()->wishlistItems()
            ->with(['product.category', 'product.brand', 'product.seller'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $wishlistItems,
        ]);
    }

    /**
     * Add product to wishlist
     */
    public function add(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to add to wishlist',
            ], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Check if already in wishlist
        $exists = Auth::user()->wishlistItems()
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist',
            ], 400);
        }

        $wishlistItem = Auth::user()->wishlistItems()->create([
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist',
            'data' => $wishlistItem->load('product'),
        ], 201);
    }

    /**
     * Remove product from wishlist
     */
    public function remove($productId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to remove from wishlist',
            ], 401);
        }

        $deleted = Auth::user()->wishlistItems()
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist',
        ]);
    }

    /**
     * Toggle product in wishlist
     */
    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to manage wishlist',
            ], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlistItem = Auth::user()->wishlistItems()
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlistItem) {
            // Remove from wishlist
            $wishlistItem->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist',
                'in_wishlist' => false,
            ]);
        } else {
            // Add to wishlist
            Auth::user()->wishlistItems()->create([
                'product_id' => $request->product_id,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist',
                'in_wishlist' => true,
            ], 201);
        }
    }

    /**
     * Check if product is in wishlist
     */
    public function check($productId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'in_wishlist' => false,
            ]);
        }

        $inWishlist = Auth::user()->wishlistItems()
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'success' => true,
            'in_wishlist' => $inWishlist,
        ]);
    }
}
