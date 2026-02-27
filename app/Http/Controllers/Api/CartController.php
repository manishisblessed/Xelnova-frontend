<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get cart contents
     */
    public function index(): JsonResponse
    {
        try {
            $cart = $this->cartService->getCartSummary();
            
            return response()->json([
                'success' => true,
                'data' => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load cart',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Add item to cart
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:10',
            'variant_id' => 'nullable|integer',
        ]);

        try {
            $cart = $this->cartService->addItem(
                $request->product_id,
                $request->quantity,
                $request->variant_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'data' => $cart,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or unavailable',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:10',
        ]);

        try {
            $cart = $this->cartService->updateItem(
                (int) $itemId,
                $request->quantity
            );

            return response()->json([
                'success' => true,
                'message' => 'Cart updated',
                'data' => $cart,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function remove($itemId): JsonResponse
    {
        try {
            $cart = $this->cartService->removeItem((int) $itemId);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'data' => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Clear entire cart
     */
    public function clear(): JsonResponse
    {
        try {
            $cart = $this->cartService->clearCart();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared',
                'data' => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        try {
            $result = $this->cartService->applyCoupon($request->coupon_code);

            if (!$result['success']) {
                return response()->json($result, 422);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(): JsonResponse
    {
        try {
            $cart = $this->cartService->removeCoupon();

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed',
                'data' => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove coupon',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get cart count (for header badge)
     */
    public function count(): JsonResponse
    {
        try {
            $count = $this->cartService->getCartCount();

            return response()->json([
                'success' => true,
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'count' => 0,
            ]);
        }
    }
}
