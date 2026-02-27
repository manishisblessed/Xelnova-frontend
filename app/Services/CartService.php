<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get or create a cart for the current user/session
     */
    public function getCart(): Cart
    {
        if (Auth::check()) {
            // Logged in user - get or create cart by user_id
            $cart = Cart::with(['items.product.seller', 'items.product.brand', 'items.variant.variantValues.productVariantOption', 'coupon'])
                ->firstOrCreate(['user_id' => Auth::id()]);
            
            // Merge any session cart into user cart
            $this->mergeSessionCart($cart);
            
            return $cart;
        }

        // Guest user - use session ID
        $sessionId = Session::getId();
        
        return Cart::with(['items.product.seller', 'items.product.brand', 'items.variant.variantValues.productVariantOption', 'coupon'])
            ->firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Merge guest cart into user cart on login
     * This is called explicitly after login with the old session ID
     */
    public function mergeGuestCartOnLogin(string $oldSessionId): void
    {
        if (!Auth::check()) {
            return;
        }

        // Get or create user cart
        $userCart = Cart::with(['items.product'])
            ->firstOrCreate(['user_id' => Auth::id()]);

        // Find guest cart using old session ID
        $guestCart = Cart::where('session_id', $oldSessionId)
            ->where('user_id', null)
            ->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        foreach ($guestCart->items as $guestItem) {
            // Check if product already in user cart
            $existingItem = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->where('variant_id', $guestItem->variant_id)
                ->first();

            if ($existingItem) {
                // Update quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $guestItem->quantity,
                ]);
            } else {
                // Move item to user cart
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        // Delete empty guest cart
        $guestCart->delete();
    }

    /**
     * Merge session cart into user cart on login
     * This is called automatically by getCart() for backward compatibility
     */
    protected function mergeSessionCart(Cart $userCart): void
    {
        $sessionId = Session::getId();
        $sessionCart = Cart::where('session_id', $sessionId)
            ->where('user_id', null)
            ->first();

        if (!$sessionCart || $sessionCart->items->isEmpty()) {
            return;
        }

        foreach ($sessionCart->items as $sessionItem) {
            // Check if product already in user cart
            $existingItem = $userCart->items()
                ->where('product_id', $sessionItem->product_id)
                ->where('variant_id', $sessionItem->variant_id)
                ->first();

            if ($existingItem) {
                // Update quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $sessionItem->quantity,
                ]);
            } else {
                // Move item to user cart
                $sessionItem->update(['cart_id' => $userCart->id]);
            }
        }

        // Delete empty session cart
        $sessionCart->delete();
    }

    /**
     * Add item to cart
     */
    public function addItem(int $productId, int $quantity = 1, ?int $variantId = null): array
    {
        $product = Product::where('is_active', true)
            ->where('status', 'approved')
            ->findOrFail($productId);

        // Get variant if specified
        $variant = null;
        if ($variantId) {
            $variant = $product->variants()->where('id', $variantId)->where('is_active', true)->first();
            if (!$variant) {
                throw new \Exception('Variant not found or inactive');
            }
        }

        // Determine price
        $price = $variant ? $variant->price : $product->price;

        $cart = $this->getCart();

        // Check if item already exists in cart
        $existingItem = $cart->items()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $quantity,
                'price' => $price, // Update price to current
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        // Refresh cart
        $cart->load(['items.product.seller', 'items.product.brand', 'items.variant.variantValues.productVariantOption', 'coupon']);

        return $this->getCartSummary($cart);
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(int $itemId, int $quantity): array
    {
        $cart = $this->getCart();
        $item = $cart->items()->findOrFail($itemId);

        if ($quantity <= 0) {
            $item->delete();
        } else {
            // Get latest price
            $item->update([
                'quantity' => $quantity,
                'price' => $item->product->price,
            ]);
        }

        // Refresh cart
        $cart->load(['items.product.seller', 'items.product.brand', 'coupon']);

        return $this->getCartSummary($cart);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $itemId): array
    {
        $cart = $this->getCart();
        $cart->items()->where('id', $itemId)->delete();

        // Refresh cart
        $cart->load(['items.product.seller', 'items.product.brand', 'coupon']);

        return $this->getCartSummary($cart);
    }

    /**
     * Clear entire cart
     */
    public function clearCart(): array
    {
        $cart = $this->getCart();
        $cart->items()->delete();
        $cart->update(['coupon_id' => null]);

        return $this->getCartSummary($cart->fresh(['items', 'coupon']));
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            return [
                'success' => false,
                'message' => 'Invalid coupon code',
            ];
        }

        if (!$coupon->isValid()) {
            return [
                'success' => false,
                'message' => 'This coupon has expired or is no longer valid',
            ];
        }

        $cart = $this->getCart();

        // Check minimum order amount
        if ($cart->subtotal < $coupon->min_order_amount) {
            return [
                'success' => false,
                'message' => "Minimum order amount of ₹{$coupon->min_order_amount} required",
            ];
        }

        // Check per-user limit if logged in
        if (Auth::check() && !$coupon->canBeUsedByUser(Auth::id())) {
            return [
                'success' => false,
                'message' => 'You have already used this coupon',
            ];
        }

        $cart->update(['coupon_id' => $coupon->id]);

        // Refresh cart
        $cart->load(['items.product.seller', 'items.product.brand', 'coupon']);

        return [
            'success' => true,
            'message' => 'Coupon applied successfully',
            'data' => $this->getCartSummary($cart),
        ];
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(): array
    {
        $cart = $this->getCart();
        $cart->update(['coupon_id' => null]);

        // Refresh cart
        $cart->load(['items.product.seller', 'items.product.brand', 'coupon']);

        return $this->getCartSummary($cart);
    }

    /**
     * Get cart summary for API response
     */
    public function getCartSummary(?Cart $cart = null): array
    {
        if (!$cart) {
            $cart = $this->getCart();
        }

        $totalTaxForDisplay = 0; // Sum of ALL tax amounts (inclusive + exclusive)
        $totalTaxToAdd = 0;      // Sum of ONLY exclusive tax amounts (to add to total)
        $totalShipping = 0;
        $totalShippingDiscount = 0;
        
        $items = $cart->items->map(function ($item) use (&$totalTaxForDisplay, &$totalTaxToAdd, &$totalShipping, &$totalShippingDiscount) {
            $product = $item->product;
            $variant = $item->variant;
            
            // Use variant data if available
            $price = $variant ? $variant->price : $product->price;
            $compareAtPrice = $variant ? $variant->compare_at_price : $product->compare_at_price;
            $stockQuantity = $variant ? $variant->quantity : $product->stock_quantity;
            $image = $variant?->main_image_url ?? $product->main_image_url;
            
            // Get variant label
            $variantLabel = null;
            if ($variant) {
                $variantLabel = $variant->variantValues
                    ->map(fn($vv) => $vv->productVariantOption->display_value)
                    ->join(' / ');
            }
            
            $discount = 0;
            if ($compareAtPrice && $compareAtPrice > $price) {
                $discount = round((($compareAtPrice - $price) / $compareAtPrice) * 100);
            }

            // Tax Calculation
            $gstRate = $product->gst_rate ?? 18; // Default to 18 if not set
            $isInclusive = $product->is_inclusive_tax;
            $quantity = $item->quantity;
            
            if ($isInclusive) {
                // Price includes tax: base = price / (1 + rate)
                $taxAmountPerUnit = $price - ($price / (1 + ($gstRate / 100)));
                // We do NOT add this to the Grand Total as it's already in the price
            } else {
                // Price excludes tax: tax = price * rate
                $taxAmountPerUnit = $price * ($gstRate / 100);
                // We MUST add this to the Grand Total
                $totalTaxToAdd += ($taxAmountPerUnit * $quantity);
            }
            
            $totalTaxForDisplay += ($taxAmountPerUnit * $quantity);

            // Shipping Calculation
            $shippingCost = $product->shipping_cost ?? 0;
            $isFreeShipping = $product->is_free_shipping ?? false;
            
            $lineShippingCost = $shippingCost * $quantity;
            
            $totalShipping += $lineShippingCost;
            if ($isFreeShipping) {
                $totalShippingDiscount += $lineShippingCost;
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_slug' => $product->slug,
                'variant_id' => $item->variant_id,
                'variant_label' => $variantLabel,
                'name' => $product->name . ($variantLabel ? ' - ' . $variantLabel : ''),
                'image' => $image,
                'price' => (float) $price,
                'original_price' => (float) $compareAtPrice,
                'discount' => $discount,
                'quantity' => $quantity,
                'total' => (float) ($price * $quantity), // Line Total (Price * Qty)
                'tax_amount' => round($taxAmountPerUnit * $quantity, 2),
                'tax_rate' => $gstRate,
                'shipping_cost' => round($lineShippingCost, 2),
                'is_free_shipping' => $isFreeShipping,
                'is_inclusive_tax' => (bool) $isInclusive,
                'seller' => [
                    'id' => $product->seller_id,
                    'name' => $product->seller?->seller?->business_name ?? $product->seller?->name ?? 'Unknown Seller',
                ],
                'in_stock' => $stockQuantity > 0,
                'stock_quantity' => $stockQuantity,
                'max_quantity' => $stockQuantity,
            ];
        });

        $subtotal = $cart->subtotal; // Sum of (Price * Qty)
        $couponDiscount = $cart->discount; // Coupon discount
        
        $netShipping = $totalShipping - $totalShippingDiscount;
        
        // Total = Subtotal - CouponDiscount + NetShipping + AdditionalTax
        $total = $subtotal - $couponDiscount + $netShipping + $totalTaxToAdd;

        return [
            'items' => $items->toArray(),
            'count' => $cart->products_count,
            'products_count' => $cart->products_count,
            'subtotal' => (float) $subtotal,
            'discount' => (float) $couponDiscount,
            'shipping_charge' => (float) $netShipping,
            'tax' => (float) $totalTaxToAdd, // Exclusive tax added to total
            'total' => (float) $total,
            'savings' => (float) $couponDiscount + $totalShippingDiscount,
            'coupon' => $cart->coupon ? [
                'code' => $cart->coupon->code,
                'name' => $cart->coupon->name,
                'discount' => (float) $couponDiscount,
            ] : null,
        ];
    }


    /**
     * Get cart count for header
     */
    public function getCartCount(): int
    {
        $cart = $this->getCart();
        return $cart->products_count; // Count of unique items, not total quantity
    }
}
