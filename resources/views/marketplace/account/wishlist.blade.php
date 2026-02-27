<x-marketplace.layout>
    @section('title', 'My Wishlist')

    <div class="bg-gray-100 py-6">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Sidebar -->
                <div class="w-full lg:w-1/4">
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b">
                            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Hello,</p>
                                <p class="font-bold text-gray-900">{{ Auth::user()->name }}</p>
                            </div>
                        </div>
                        <nav class="space-y-1">
                            <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                                My Orders
                            </a>
                            <a href="{{ route('account.wishlist') }}" class="flex items-center gap-3 px-3 py-2 bg-blue-50 text-blue-600 rounded font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                                My Wishlist
                            </a>
                            <a href="{{ route('account.profile') }}" class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                My Profile
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Wishlist Items -->
                <div class="w-full lg:w-3/4">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 border-b">
                            <h1 class="text-xl font-bold text-gray-900">My Wishlist</h1>
                        </div>

                        @if($wishlistItems->isEmpty())
                            <!-- Empty State -->
                            <div class="p-12 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-24 h-24 mx-auto text-gray-300 mb-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                                <h2 class="text-2xl font-medium text-gray-600 mb-2">Your wishlist is empty</h2>
                                <p class="text-gray-500 mb-6">Save your favorite products to buy them later.</p>
                                <a href="{{ route('marketplace.products') }}" class="inline-block bg-xelnova-green-600 hover:bg-xelnova-green-700 text-white font-bold py-3 px-8 rounded shadow-sm transition">
                                    Browse Products
                                </a>
                            </div>
                        @else
                            <!-- Wishlist Grid -->
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-data="wishlistPage()">
                                @foreach($wishlistItems as $item)
                                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition group" x-data="{ removing: false }">
                                        <!-- Product Image -->
                                        <a href="{{ route('marketplace.product.detail', $item->product->slug) }}" class="block relative bg-gray-100 aspect-square">
                                            <img src="{{ $item->product->main_image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-contain">
                                            
                                            <!-- Remove Button -->
                                            <button @click.prevent="removeFromWishlist({{ $item->product_id }})" 
                                                    :disabled="removing"
                                                    class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-red-50 transition">
                                                <svg x-show="!removing" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-500">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                                <svg x-show="removing" class="animate-spin h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            </button>
                                        </a>

                                        <!-- Product Info -->
                                        <div class="p-3">
                                            <a href="{{ route('marketplace.product.detail', $item->product->slug) }}" class="block">
                                                <h3 class="font-medium text-gray-900 text-sm mb-1 line-clamp-2 hover:text-blue-600">{{ $item->product->name }}</h3>
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="text-lg font-bold text-gray-900">{{ $item->product->formatted_price }}</span>
                                                    @if($item->product->compare_at_price)
                                                        <span class="text-sm text-gray-500 line-through">₹{{ number_format($item->product->compare_at_price, 2) }}</span>
                                                        <span class="text-xs text-green-600 font-bold">{{ $item->product->discount_percentage }}% OFF</span>
                                                    @endif
                                                </div>
                                            </a>
                                            
                                            <!-- Add to Cart Button -->
                                            <button @click="addToCart({{ $item->product_id }})" 
                                                    class="w-full bg-xelnova-gold-500 hover:bg-xelnova-gold-600 text-white font-bold py-2 px-4 rounded text-sm transition">
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function wishlistPage() {
            return {
                async removeFromWishlist(productId) {
                    try {
                        const response = await fetch(`/api/v1/wishlist/${productId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Reload page to update wishlist
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Error removing from wishlist:', error);
                    }
                },

                async addToCart(productId) {
                    try {
                        const response = await fetch('/api/v1/cart/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                product_id: productId,
                                quantity: 1
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update cart count and show success message
                            window.dispatchEvent(new CustomEvent('cart-updated'));
                            
                            // Optional: Show toast notification
                            alert('Product added to cart!');
                        }
                    } catch (error) {
                        console.error('Error adding to cart:', error);
                    }
                }
            };
        }
    </script>
</x-marketplace.layout>
