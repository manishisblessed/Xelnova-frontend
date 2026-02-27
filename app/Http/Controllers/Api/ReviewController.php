<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Get reviews for a product
     */
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->with('user')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Submit a review
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to submit a review',
            ], 401);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        // Check if user has purchased this product
        $hasPurchased = Order::where('user_id', Auth::id())
            ->whereHas('items', function ($q) use ($validated) {
                $q->where('product_id', $validated['product_id']);
            })
            ->where('order_status', 'delivered')
            ->exists();

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product',
            ], 400);
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'product_id' => $validated['product_id'],
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'comment' => $validated['comment'],
            'is_verified_purchase' => $hasPurchased,
            'is_approved' => true, // Auto-approve for now
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review->load('user'),
        ], 201);
    }

    /**
     * Update a review
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to update review',
            ], 401);
        }

        $review = Review::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        $review->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review->load('user'),
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to delete review',
            ], 401);
        }

        $review = Review::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Check if user can review a product
     */
    public function canReview($productId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'can_review' => false,
                'reason' => 'not_logged_in',
            ]);
        }

        // Check if already reviewed
        $hasReviewed = Review::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->exists();

        if ($hasReviewed) {
            return response()->json([
                'success' => true,
                'can_review' => false,
                'reason' => 'already_reviewed',
            ]);
        }

        // Check if purchased and delivered
        $hasPurchased = Order::where('user_id', Auth::id())
            ->whereHas('items', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->where('order_status', 'delivered')
            ->exists();

        return response()->json([
            'success' => true,
            'can_review' => $hasPurchased,
            'is_verified_purchase' => $hasPurchased,
        ]);
    }
}
