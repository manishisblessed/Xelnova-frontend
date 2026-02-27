<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SubOrder;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Create an order from cart
     */
    public function createOrderFromCart(Cart $cart, array $data): Order
    {
        return DB::transaction(function () use ($cart, $data) {
            // Pre-calculate totals by iterating items
            $subtotalLookup = 0;
            $totalTaxToAdd = 0;
            $totalTaxForDisplay = 0; // Initialize display tax accumulator
            $totalShipping = 0;
            $totalShippingDiscount = 0;
            
            $itemCalculations = [];

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                $variant = $cartItem->variant;
                $price = $variant ? $variant->price : $product->price;
                $quantity = $cartItem->quantity;

                // Tax Logic
                $gstRate = $product->gst_rate ?? 18;
                $isInclusive = $product->is_inclusive_tax;
                $taxAmountPerUnit = 0;
                $taxToAdd = 0;

                if ($isInclusive) {
                     // Tax included in price.
                     $taxAmountPerUnit = $price - ($price / (1 + ($gstRate / 100)));
                     // taxToAdd is 0 because price is already inclusive
                } else {
                     // Tax is extra.
                     $taxAmountPerUnit = $price * ($gstRate / 100);
                     $taxToAdd = $taxAmountPerUnit * $quantity;
                }
                
                // Shipping Logic
                $shippingCost = $product->shipping_cost ?? 0;
                $isFreeShipping = $product->is_free_shipping ?? false;
                $lineShippingCost = $shippingCost * $quantity;
                
                $totalShipping += $lineShippingCost;
                if ($isFreeShipping) {
                    $totalShippingDiscount += $lineShippingCost;
                }
                
                $totalTaxToAdd += $taxToAdd;
                $totalTaxForDisplay += ($taxAmountPerUnit * $quantity); // Accumulate all tax (inclusive + exclusive)
                
                // Store calc for later use
                $itemCalculations[$cartItem->id] = [
                    'tax_amount' => $taxAmountPerUnit * $quantity, // Total tax for this line
                    'tax_rate' => $gstRate,
                    'shipping_cost' => $lineShippingCost,
                    'is_free_shipping' => $isFreeShipping,
                    'is_inclusive_tax' => $isInclusive,
                ];
            }
            
            $subtotal = $cart->subtotal;
            $couponDiscount = $cart->discount;
            $netShipping = $totalShipping - $totalShippingDiscount;
            
            // Total = Subtotal - Discount + NetShipping + AdditionalTax
            $total = ($subtotal - $couponDiscount) + $netShipping + $totalTaxToAdd;

            // Create order (Note: 'tax' column in orders table usually represents the total tax amount. 
            // Whether it was inclusive or exclusive, it's good to record the total tax component.)
            // However, strictly speaking, if we add $totalTaxToAdd here, the order 'total' matches payment. 
            // The 'tax' column might be expected to be just the added tax or full tax? 
            // Let's store the Total Tax (Inclusive + Exclusive) or just Exclusive?
            // Usually 'tax' column in Order header is "Total Tax Amount". 
            // But if I store Inclusive+Exclusive there, and do Total = Subtotal + Tax, I double count inclusive.
            // So `tax` column ideally should be "Additional Tax" OR we change logic.
            // Let's store "Additional Tax" (Exclusive) in `tax` column for now to make `total` math work:
            // Total = Subtotal + Shipping + Tax - Discount.
            // If Subtotal is inclusive, Tax should be 0 (or strictly the extra tax).
            
            // Actually, we should check what `calculateTax` used to do. It did `subtotal * 0.18`. It added it.
            // So existing orders expect `tax` to be added to subtotal.
            // So I will store `$totalTaxToAdd` in the `tax` column.
            
            $order = Order::create([
                'user_id' => $cart->user_id,
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? $data['shipping_address'],
                'subtotal' => $subtotal,
                'discount' => $couponDiscount,
                'shipping_charge' => $netShipping,
                'tax' => $totalTaxForDisplay, // Storing Total calculated tax (Inclusive + Exclusive) for UI display
                'total' => $total,
                'payment_method' => $data['payment_method'] ?? 'razorpay',
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'coupon_id' => $cart->coupon_id,
                'coupon_code' => $cart->coupon?->code,
                'coupon_discount' => $couponDiscount,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create order items
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                $variant = $cartItem->variant;
                $calcs = $itemCalculations[$cartItem->id];

                // Prepare variant details
                $variantDetails = null;
                $productImage = $product->main_image;

                if ($variant) {
                    $variantDetails = [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'label' => $variant->variantValues->map(fn($vv) => $vv->productVariantOption->display_value)->join(' / '),
                        'options' => $variant->variantValues->map(function($vv) {
                            return [
                                'type' => $vv->productVariantOption->variantType->name,
                                'value' => $vv->productVariantOption->display_value,
                            ];
                        })->toArray(),
                    ];

                    if ($variant->main_image) {
                        $productImage = $variant->main_image;
                    }
                }

                Log::info('Order Item Creation Debug', [
                    'product_id' => $product->id,
                    'variant_id' => $cartItem->variant_id,
                    'variant_found' => (bool) $variant,
                    'variant_details' => $variantDetails,
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $cartItem->variant_id,
                    'variant_details' => $variantDetails,
                    'seller_id' => $product->seller_id,
                    'product_name' => $product->name,
                    'product_image' => $productImage,
                    'product_options' => null, // Legacy field
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'total' => $cartItem->total,
                    'status' => 'pending',
                    // NEW FIELDS
                    'tax_amount' => $calcs['tax_amount'], 
                    'tax_rate' => $calcs['tax_rate'], 
                    'shipping_cost' => $calcs['shipping_cost'], 
                    'is_free_shipping' => $calcs['is_free_shipping'],
                    'is_inclusive_tax' => $calcs['is_inclusive_tax'], // Store tax behavior
                ]);

                // Update stock
                if ($variant) {
                    try {
                        $newQuantity = max(0, $variant->quantity - $cartItem->quantity);
                        $variant->update([
                            'quantity' => $newQuantity,
                            'stock_status' => $newQuantity > 0 ? 'in_stock' : 'out_of_stock',
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to update variant stock: ' . $e->getMessage());
                    }
                } else {
                    $this->updateProductStock($product, $cartItem->quantity);
                }
            }

            // Increment coupon usage if applicable
            if ($cart->coupon) {
                $cart->coupon->incrementUsage();
            }

            // Clear cart
            $cart->items()->delete();
            $cart->update(['coupon_id' => null]);

            return $order;
        });
    }

    /**
     * Calculate tax (GST) NO LONGER USED BUT KEPT FOR INTERFACE COMPATIBILITY 
     * OR REMOVE IF SAFE. Since it's protected, we can ignore it.
     */
    protected function calculateTax(float $amount): float
    {
        return round($amount * 0.18, 2);
    }

    /**
     * Update product stock after order
     */
    protected function updateProductStock(Product $product, int $quantity): void
    {
        try {
            $newQuantity = max(0, $product->quantity - $quantity);
            $product->update([
                'quantity' => $newQuantity,
                'stock_status' => $newQuantity > 0 ? 'in_stock' : 'out_of_stock',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update product stock: ' . $e->getMessage());
        }
    }

    /**
     * Process payment and create sub-orders
     */
    public function processPayment(Order $order, array $paymentData): bool
    {
        try {
            $processed = DB::transaction(function () use ($order, $paymentData) {
                // Update payment details
                $order->update([
                    'payment_id' => $paymentData['payment_id'] ?? null,
                    'payment_status' => 'paid',
                    'order_status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                // Create sub-orders (split by seller)
                $this->createSubOrders($order);

                return true;
            });

            if ($processed) {
                $this->notifyOrderAndPaymentSuccess($order);
            }

            return $processed;
        } catch (\Exception $e) {
            Log::error('Payment processing failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create sub-orders by grouping items by seller
     */
    protected function createSubOrders(Order $order): void
    {
        // Group items by seller
        $itemsBySeller = $order->items->groupBy('seller_id');
        
        $index = 1;
        foreach ($itemsBySeller as $sellerId => $items) {
            // Calculate totals for this seller from stored item values
            $subtotal = $items->sum('total');
            $taxTotal = 0; // Total tax (inclusive + exclusive) stored in tax_amount
            $shippingTotal = 0;
            
            // We need to figure out "Additional Tax" for the `tax` column to be consistent with Order `tax` column logic.
            // But we stored `tax_amount` which is the calculated tax.
            // We don't strictly know if it was inclusive or exclusive looking ONLY at `order_items` (unless we check price vs total).
            
            // Wait, we need to know how much tax was ADDED to the total.
            // Since we didn't store "is_inclusive" in order_items, we can infer it?
            // If we assume `tax` column in SubOrder should match logic of Order `tax`, it should be the "Extra Tax".
            // However, relying on re-calculation might be safer or we just Sum `tax_amount`?
            // If we sum `tax_amount`, we get the total tax value.
            // If the item was inclusive, that tax amount is PART of the subtotal (price*qty).
            // If exclusive, it's ON TOP.
            
            // Let's recalculate simply to be safe, or check product?
            // Product might have changed.
            // Ideally `order_items` should store `tax_type` (inclusive/exclusive). I didn't add that column.
            
            // But wait, `total` in order_items is `price * quantity`.
            // If inclusive, `price` includes tax.
            // If exclusive, `price` excludes tax.
            // So `total` is consistent with `subtotal`.
            
            // The `Order` total was calculated as `Subtotal + NetShipping + ExtraTax`.
            
            // Let's re-examine `createOrderFromCart`.
            // There I calculated `$totalTaxToAdd`.
            
            // Issue: When splitting into SubOrders, I need to know how much of `tax_amount` was "Extra".
            // Since I didn't add a column for `tax_behavior` or `is_tax_inclusive` in `order_items`, I am slightly flying blind here unless I query Product again (which might have changed).
            // BUT, usually once ordered, the snapshot is what matters.
            
            // WORKAROUND: `tax_amount` currently stores the calculated tax.
            // I'll assume for `SubOrder` `tax` column, we want the Total Tax Amount involved, 
            // OR we want the Extra Tax?
            // Given the invoice requirement "Tax Rate | Tax Type | Tax Amount", we probably want to show the tax regardless of inclusive/exclusive.
            // But for `sub_orders.total` calculation, we need to know what to add.
            
            // If I look at `amount` = `subtotal` + `shipping` + `tax`.
            // This formula implies `tax` is additive.
            // If `subtotal` is inclusive, adding `tax` again is wrong.
            
            // Solution: 
            // I'll query the product snapshot? No.
            // I will check `tax_rate`. 
            // Actually, if `tax_amount` > 0 and `total` (price*qty) seems to account for it...
            
            // Let's change `createSubOrders` to do this:
            // Iterate items.
            // For each item, I really need to know if it was inclusive.
            // Since I can't know for sure without the flag, I will fetch the Product again just to check the flag?
            // Yes, that's partial fix.
            
            // Better fix: Add `is_inclusive_tax` to `order_items`? 
            // I already ran migration. I prefer not to add more columns if I can avoid it.
            // Wait, `price` is stored. `tax_rate` is stored. `tax_amount` is stored.
            // If Inclusive: `tax_amount` = `price` - (`price` / (1+rate)).
            // If Exclusive: `tax_amount` = `price` * rate.
            // Calculate both. See which one matches `tax_amount` with small epsilon.
            
            $subOrderTotal = 0;
            $subOrderTaxToAdd = 0;     // For calculating Total (Exclusive only)
            $subOrderDisplayTax = 0;   // For Display in UI (Inclusive + Exclusive)
            $subOrderNetShipping = 0;
            
            foreach ($items as $item) {
                // Shipping
                $itemShipping = $item->shipping_cost;
                if ($item->is_free_shipping) {
                    $itemShipping = 0; // Net is 0
                }
                $subOrderNetShipping += $itemShipping;
                
                // Tax
                $storedTax = $item->tax_amount;
                $subOrderDisplayTax += $storedTax; // Always accumulate for display

                // Check if tax is inclusive or exclusive to know if we should add it to total
                $rate = $item->tax_rate;
                $price = $item->price;
                $qty = $item->quantity;
                
                $expectedExclusiveTax = round(($price * $qty) * ($rate / 100), 2);
                
                // Check if stored tax matches exclusive formula
                if (abs($storedTax - $expectedExclusiveTax) < 0.05) {
                    // It was likely Exclusive. So we add it to the Total.
                    $subOrderTaxToAdd += $storedTax;
                }
            }
            
            // Total = Subtotal + Shipping + Extra Tax
            $total = $subtotal + $subOrderNetShipping + $subOrderTaxToAdd;

            // Create sub-order
            $subOrder = SubOrder::create([
                'sub_order_number' => SubOrder::generateSubOrderNumber($order, $index),
                'order_id' => $order->id,
                'seller_id' => $sellerId,
                'subtotal' => $subtotal,
                'shipping_charge' => $subOrderNetShipping,
                'tax' => $subOrderDisplayTax, // Storing Total Tax (Inclusive + Exclusive)
                'total' => $total,
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            // Update order items with sub_order_id
            foreach ($items as $item) {
                $item->update([
                    'sub_order_id' => $subOrder->id,
                    'status' => 'confirmed',
                ]);
            }

            $index++;
        }

        Log::info("Created {$index} sub-orders for order {$order->order_number}");
    }

    /**
     * Handle payment failure
     */
    public function handlePaymentFailure(Order $order): void
    {
        $order->update([
            'payment_status' => 'failed',
            'order_status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Restore product stock
        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product) {
                $product->increment('quantity', $item->quantity);
                if ($product->quantity > 0) {
                    $product->update(['stock_status' => 'in_stock']);
                }
            }
        }

        $this->notifyPaymentFailure($order);
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order): bool
    {
        if (!$order->canBeCancelled()) {
            return false;
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Restore product stock
            foreach ($order->items as $item) {
                $item->update(['status' => 'cancelled']);
                
                $product = $item->product;
                if ($product) {
                    $product->increment('quantity', $item->quantity);
                    if ($product->quantity > 0) {
                        $product->update(['stock_status' => 'in_stock']);
                    }
                }
            }
        });

        return true;
    }

    protected function notifyOrderAndPaymentSuccess(Order $order): void
    {
        try {
            $phone = $this->resolveCustomerPhone($order);
            if (!$phone) {
                return;
            }

            $amount = $this->formatAmountForSms((float) $order->total);

            $this->smsService->sendOrderPlaced(
                $phone,
                $order->order_number,
                $amount
            );

            $this->smsService->sendPaymentSuccessful(
                $phone,
                $order->order_number,
                $amount
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send order/payment success SMS', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function notifyPaymentFailure(Order $order): void
    {
        try {
            $phone = $this->resolveCustomerPhone($order);
            if (!$phone) {
                return;
            }

            $this->smsService->sendPaymentFailed(
                $phone,
                $order->order_number,
                route('marketplace.checkout')
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send payment failure SMS', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function resolveCustomerPhone(Order $order): ?string
    {
        $order->loadMissing('user');

        return $order->user?->phone
            ?? ($order->shipping_address['phone'] ?? null)
            ?? ($order->billing_address['phone'] ?? null);
    }

    protected function formatAmountForSms(float $amount): string
    {
        $formatted = number_format($amount, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }
}
