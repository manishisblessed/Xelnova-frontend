@props(['product'])

<div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 border border-gray-100 overflow-hidden group h-full flex flex-col"
     x-data="productCard({{ $product['id'] ?? 1 }})">
    <!-- Image -->
    <div class="relative aspect-[4/5] overflow-hidden bg-gray-100">
        <a href="{{ route('marketplace.product.detail', $product['slug'] ?? 'product') }}" class="block w-full h-full">
            <img src="{{ $product['image'] ?? 'https://placehold.co/400x500?text=Product' }}" 
                 alt="{{ $product['name'] ?? 'Product' }}" 
                 class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
        </a>
        
        <!-- Wishlist Button -->
        <button @click="toggleWishlist" 
                :disabled="isLoading"
                class="absolute top-3 right-3 p-2 rounded-full bg-white/90 hover:bg-white transition shadow-sm z-10 disabled:opacity-50"
                :class="isInWishlist ? 'text-red-500' : 'text-gray-400 hover:text-red-500'">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"
                 :fill="isInWishlist ? 'currentColor' : 'none'">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
            </svg>
        </button>
        
        @if(isset($product['discount']))
            <span class="absolute top-3 left-3 bg-green-600 text-white text-xs font-bold px-2 py-1 rounded z-10">
                {{ $product['discount'] }}% OFF
            </span>
        @endif
    </div>

    <!-- Content -->
    <div class="p-4 flex-grow flex flex-col">
        <!-- Brand -->
        @if(isset($product['brand']))
            <p class="text-xs text-gray-500 font-medium mb-1">{{ $product['brand'] }}</p>
        @endif
        
        <!-- Title -->
        <h3 class="text-sm font-medium text-gray-900 line-clamp-2 mb-2 hover:text-xelnova-green-600 transition">
            <a href="{{ route('marketplace.product.detail', $product['slug'] ?? 'product') }}">{{ $product['name'] ?? 'Product Name' }}</a>
        </h3>
        
        <!-- Rating -->
        <div class="flex items-center gap-2 mb-2">
            <div class="flex items-center bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded gap-1">
                <span>{{ $product['rating'] ?? '4.5' }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3">
                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                </svg>
            </div>
            <span class="text-xs text-gray-500">({{ $product['reviews_count'] ?? rand(50, 5000) }})</span>
        </div>
        
        <!-- Price -->
        <div class="mt-auto">
            <div class="flex items-baseline gap-2">
                <span class="text-lg font-bold text-gray-900">₹{{ number_format($product['price'] ?? 999) }}</span>
                @if(isset($product['original_price']))
                    <span class="text-sm text-gray-500 line-through">₹{{ number_format($product['original_price']) }}</span>
                @endif
            </div>
            
            @if(isset($product['delivery_text']))
                <p class="text-xs text-gray-500 mt-1">{{ $product['delivery_text'] }}</p>
            @else
                <p class="text-xs text-green-600 font-medium mt-1">Free delivery</p>
            @endif
        </div>
    </div>
</div>

<script>
function productCard(productId) {
    return {
        productId: productId,
        isInWishlist: false,
        isLoading: false,

        init() {
            // Don't check on load to avoid rate limiting
            // Status will be updated after first toggle
        },

        async toggleWishlist() {
            this.isLoading = true;

            try {
                const response = await fetch('/api/v1/wishlist/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ product_id: this.productId })
                });

                const data = await response.json();

                if (data.success) {
                    this.isInWishlist = data.in_wishlist;
                    
                    // Show toast notification
                    this.showToast(
                        this.isInWishlist ? 'Added to wishlist' : 'Removed from wishlist',
                        'success'
                    );
                } else {
                    // User might not be logged in
                    if (response.status === 401) {
                        this.showToast('Please login to add to wishlist', 'error');
                        setTimeout(() => {
                            window.location.href = '/customer/login';
                        }, 1500);
                    } else {
                        this.showToast(data.message || 'Failed to update wishlist', 'error');
                    }
                }
            } catch (error) {
                this.showToast('Failed to update wishlist', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        showToast(message, type = 'success') {
            // Dispatch custom event for toast notification
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    };
}
</script>

