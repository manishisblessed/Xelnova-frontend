<x-marketplace.layout>
    @section('title', 'Shopping Cart')
    @php
        $isSellerAccount = auth()->check() && auth()->user()->isSeller();
    @endphp

    <div class="bg-gray-100 py-6" x-data="cartPage()">
        <div class="container mx-auto px-4">
            @if($isSellerAccount)
                <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-800">
                    Seller accounts cannot place marketplace orders. Please login with a customer account to continue checkout.
                </div>
            @endif

            <!-- Loading State -->
            <div x-show="loading" class="flex justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-xelnova-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Empty Cart State -->
            <div x-show="!loading && cart.items.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-24 h-24 mx-auto text-gray-300 mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
                <h2 class="text-2xl font-medium text-gray-600 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Looks like you haven't added anything to your cart yet.</p>
                <a href="{{ route('marketplace.products') }}" class="inline-block bg-xelnova-green-500 hover:bg-xelnova-green-600 text-white font-bold py-3 px-8 rounded shadow-sm transition">
                    Start Shopping
                </a>
            </div>

            <!-- Cart Content -->
            <div x-show="!loading && cart.items.length > 0" x-cloak>
                <h1 class="text-2xl font-bold text-gray-800 mb-6">
                    Shopping Cart (<span x-text="cart.count"></span> <span x-text="cart.count === 1 ? 'item' : 'items'"></span>)
                </h1>

                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Cart Items -->
                    <div class="w-full lg:w-2/3">
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <!-- Cart Items List -->
                            <template x-for="item in cart.items" :key="item.id">
                                <div class="p-4 border-b flex flex-col sm:flex-row gap-4">
                                    <div class="w-24 h-24 flex-shrink-0">
                                        <img :src="item.image || '/images/placeholder-product.png'" :alt="item.name" class="w-full h-full object-contain">
                                    </div>
                                    <div class="flex-1">
                                        <a :href="'/product/' + item.product_slug" class="text-base font-medium text-gray-900 mb-1 hover:text-xelnova-green-600" x-text="item.name"></a>
                                        <p class="text-xs text-gray-500 mb-2">Seller: <span x-text="item.seller.name"></span></p>
                                        <div class="flex flex-col mb-4">
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-lg font-bold text-gray-900" x-text="'₹' + item.price.toLocaleString('en-IN')"></span>
                                                <span class="text-sm text-gray-500 line-through" x-show="item.original_price" x-text="'₹' + (item.original_price || 0).toLocaleString('en-IN')"></span>
                                                <span class="text-green-600 text-sm font-bold" x-show="item.discount > 0" x-text="item.discount + '% Off'"></span>
                                            </div>
                                            <span class="text-[10px] uppercase font-bold mt-1" 
                                                  :class="item.is_inclusive_tax ? 'text-gray-500' : 'text-orange-600'" 
                                                  x-text="item.is_inclusive_tax ? '(Tax Inclusive)' : '+ ' + item.tax_rate + '% Tax'">
                                            </span>
                                            <span class="text-[10px] uppercase font-bold mt-1" 
                                                  :class="item.is_free_shipping ? 'text-green-600' : 'text-gray-500'" 
                                                  x-text="item.is_free_shipping ? 'Free Shipping' : '+ ₹' + item.shipping_cost + ' Shipping'">
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-6">
                                            <div class="flex items-center gap-2">
                                                <button 
                                                    @click="updateQuantity(item.id, item.quantity - 1)" 
                                                    :disabled="item.quantity <= 1 || updatingItem === item.id"
                                                    class="w-7 h-7 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50">
                                                    <span x-show="updatingItem !== item.id">-</span>
                                                    <svg x-show="updatingItem === item.id" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                </button>
                                                <input type="text" :value="item.quantity" readonly class="w-12 text-center border border-gray-300 rounded py-1 text-sm bg-gray-50">
                                                <button 
                                                    @click="updateQuantity(item.id, item.quantity + 1)" 
                                                    :disabled="item.quantity >= 10 || updatingItem === item.id"
                                                    class="w-7 h-7 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50">
                                                    <span x-show="updatingItem !== item.id">+</span>
                                                    <svg x-show="updatingItem === item.id" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <button 
                                                @click="removeItem(item.id)" 
                                                :disabled="removingItem === item.id"
                                                class="text-sm font-medium text-gray-800 hover:text-red-600 flex items-center gap-1">
                                                <svg x-show="removingItem === item.id" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                                REMOVE
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500 sm:text-right">
                                        <span x-show="item.in_stock" class="text-green-600">In Stock</span>
                                        <span x-show="!item.in_stock" class="text-red-600">Out of Stock</span>
                                    </div>
                                </div>
                            </template>

                            <!-- Place Order Button (Mobile Sticky) -->
                            <div class="p-4 bg-white shadow-[0_-2px_10px_rgba(0,0,0,0.1)] sticky bottom-0 lg:static flex justify-end">
                                @if($isSellerAccount)
                                    <button type="button" disabled class="bg-gray-300 text-gray-700 font-bold py-3 px-10 rounded shadow-sm uppercase text-sm cursor-not-allowed">
                                        Seller Account Restricted
                                    </button>
                                @else
                                    <a href="{{ route('marketplace.checkout') }}" class="bg-xelnova-gold-500 hover:bg-xelnova-gold-600 text-white font-bold py-3 px-10 rounded shadow-sm transition uppercase text-sm">
                                        Place Order
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Price Details -->
                    <div class="w-full lg:w-1/3">
                        <div class="bg-white rounded-lg shadow-sm p-4 sticky top-24">
                            <h2 class="text-gray-500 font-medium uppercase text-sm border-b pb-3 mb-4">Price Details</h2>
                            
                            <div class="space-y-4 mb-4 border-b pb-4">
                                <div class="flex justify-between text-gray-800">
                                    <span>Price (<span x-text="cart.products_count"></span> <span x-text="cart.products_count === 1 ? 'item' : 'items'"></span>)</span>
                                    <span x-text="'₹' + cart.subtotal.toLocaleString('en-IN')"></span>
                                </div>
                                <div class="flex justify-between text-gray-800" x-show="cart.discount > 0">
                                    <span>Discount</span>
                                    <span class="text-green-600" x-text="'- ₹' + cart.discount.toLocaleString('en-IN')"></span>
                                </div>
                                <div class="flex justify-between text-gray-800" x-show="cart.tax > 0">
                                    <span>Tax (GST)</span>
                                    <span x-text="'₹' + cart.tax.toLocaleString('en-IN')"></span>
                                </div>
                                <div class="flex justify-between text-gray-800">
                                    <span>Delivery Charges</span>
                                    <span :class="cart.shipping_charge > 0 ? '' : 'text-green-600'" x-text="cart.shipping_charge > 0 ? '₹' + cart.shipping_charge.toLocaleString('en-IN') : 'Free'"></span>
                                </div>
                            </div>

                            <div class="flex justify-between text-lg font-bold text-gray-900 mb-4 border-b border-dashed pb-4">
                                <span>Total Amount</span>
                                <span x-text="'₹' + cart.total.toLocaleString('en-IN')"></span>
                            </div>

                            <p class="text-green-600 font-medium text-sm" x-show="cart.savings > 0">
                                You will save ₹<span x-text="cart.savings.toLocaleString('en-IN')"></span> on this order
                            </p>
                            
                            <!-- Coupon -->
                            <div class="mt-6 pt-4 border-t border-dashed">
                                <!-- Applied Coupon -->
                                <div x-show="cart.coupon" class="flex justify-between items-center bg-green-50 p-3 rounded mb-3">
                                    <div>
                                        <span class="font-medium text-green-700" x-text="cart.coupon?.code"></span>
                                        <span class="text-sm text-green-600 ml-2">applied</span>
                                    </div>
                                    <button @click="removeCoupon" :disabled="applyingCoupon" class="text-red-600 text-sm font-medium hover:text-red-700">
                                        Remove
                                    </button>
                                </div>

                                <!-- Apply Coupon Form -->
                                <div x-show="!cart.coupon" class="flex gap-2">
                                    <input 
                                        type="text" 
                                        x-model="couponCode"
                                        placeholder="Enter Coupon Code" 
                                        class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:ring-xelnova-green-500 focus:border-xelnova-green-500 uppercase"
                                        @keyup.enter="applyCoupon">
                                    <button 
                                        @click="applyCoupon" 
                                        :disabled="!couponCode || applyingCoupon"
                                        class="text-xelnova-green-600 font-bold text-sm hover:text-xelnova-green-700 disabled:opacity-50 disabled:cursor-not-allowed px-3">
                                        <span x-show="!applyingCoupon">APPLY</span>
                                        <svg x-show="applyingCoupon" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p x-show="couponError" class="text-red-500 text-sm mt-2" x-text="couponError"></p>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex items-center gap-2 text-gray-500 text-xs justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                            <span>Safe and Secure Payments. 100% Authentic products.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cartPage() {
            return {
                loading: true,
                cart: {
                    items: [],
                    count: 0,
                    products_count: 0,
                    subtotal: 0,
                    discount: 0,
                    shipping_charge: 0,
                    total: 0,
                    savings: 0,
                    coupon: null
                },
                couponCode: '',
                couponError: '',
                applyingCoupon: false,
                updatingItem: null,
                removingItem: null,

                async init() {
                    await this.fetchCart();
                },

                async fetchCart() {
                    try {
                        const response = await fetch('/api/v1/cart', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.cart = data.data;
                        }
                    } catch (error) {
                        console.error('Error fetching cart:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async updateQuantity(itemId, quantity) {
                    if (quantity < 1 || quantity > 10) return;
                    this.updatingItem = itemId;

                    try {
                        const response = await fetch(`/api/v1/cart/item/${itemId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ quantity })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.cart = data.data;
                            this.updateHeaderCart();
                        }
                    } catch (error) {
                        console.error('Error updating quantity:', error);
                    } finally {
                        this.updatingItem = null;
                    }
                },

                async removeItem(itemId) {
                    this.removingItem = itemId;

                    try {
                        const response = await fetch(`/api/v1/cart/item/${itemId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.cart = data.data;
                            this.updateHeaderCart();
                        }
                    } catch (error) {
                        console.error('Error removing item:', error);
                    } finally {
                        this.removingItem = null;
                    }
                },

                async applyCoupon() {
                    if (!this.couponCode) return;
                    this.applyingCoupon = true;
                    this.couponError = '';

                    try {
                        const response = await fetch('/api/v1/cart/coupon', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ coupon_code: this.couponCode })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.cart = data.data;
                            this.couponCode = '';
                        } else {
                            this.couponError = data.message;
                        }
                    } catch (error) {
                        console.error('Error applying coupon:', error);
                        this.couponError = 'Failed to apply coupon';
                    } finally {
                        this.applyingCoupon = false;
                    }
                },

                async removeCoupon() {
                    this.applyingCoupon = true;

                    try {
                        const response = await fetch('/api/v1/cart/coupon', {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.cart = data.data;
                        }
                    } catch (error) {
                        console.error('Error removing coupon:', error);
                    } finally {
                        this.applyingCoupon = false;
                    }
                },

                updateHeaderCart() {
                    // Dispatch event to update header cart count
                    window.dispatchEvent(new CustomEvent('cart-updated', {
                        detail: { count: this.cart.count }
                    }));
                }
            };
        }
    </script>
</x-marketplace.layout>
