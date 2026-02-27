<x-marketplace.layout>
    @section('title', 'Products')

<div class="bg-gray-100 py-4">
    <div class="container mx-auto px-4">
        <!-- Breadcrumbs -->
        <div class="text-xs text-gray-500 mb-4">
            <a href="{{ route('home') }}" class="hover:text-xelnova-green-600">Home</a>
            <span class="mx-1">›</span>
            <span class="text-gray-700 font-medium">Products</span>
        </div>

        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Sidebar Filters -->
            <div class="w-full lg:w-1/5 flex-shrink-0">
                <form method="GET" action="{{ route('marketplace.products') }}" id="filterForm">
                    <div class="bg-white rounded-lg shadow-sm p-4 sticky top-24">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h2 class="text-lg font-bold text-gray-800">Filters</h2>
                            <a href="{{ route('marketplace.products') }}" class="text-xs text-xelnova-green-600 font-medium hover:underline">Clear All</a>
                        </div>

                        <!-- Search -->
                        <div class="mb-6">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="w-full text-sm border-gray-300 rounded-lg focus:ring-xelnova-green-500 focus:border-xelnova-green-500">
                        </div>

                        <!-- Categories -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2 uppercase">Categories</h3>
                            <ul class="text-sm space-y-2 text-gray-600 max-h-64 overflow-y-auto">
                                @foreach($categories as $category)
                                    <li>
                                        <a href="{{ route('marketplace.products', ['category' => $category->slug] + request()->except('category')) }}" 
                                           class="hover:text-xelnova-green-600 {{ request('category') == $category->slug ? 'font-medium text-xelnova-green-600' : '' }}">
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Price -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2 uppercase">Price Range</h3>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full text-xs border-gray-300 rounded">
                                <span class="text-gray-400">to</span>
                                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full text-xs border-gray-300 rounded">
                            </div>
                            <button type="submit" class="w-full mt-2 bg-xelnova-green-500 text-white text-xs py-2 rounded hover:bg-xelnova-green-600">Apply</button>
                        </div>

                        <!-- Brand -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2 uppercase">Brand</h3>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach($brands as $brand)
                                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                        <input type="checkbox" name="brand[]" value="{{ $brand->id }}" 
                                               {{ in_array($brand->id, (array)request('brand', [])) ? 'checked' : '' }}
                                               onchange="document.getElementById('filterForm').submit()"
                                               class="rounded text-xelnova-green-600 focus:ring-xelnova-green-500">
                                        <span>{{ $brand->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Product Grid with Infinite Scroll -->
            <div class="w-full lg:w-4/5" 
                 x-data="infiniteProducts()" 
                 x-init="init()">
                
                <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <h1 class="text-lg font-bold text-gray-800">
                            Products 
                            <span class="text-sm font-normal text-gray-500" x-text="'(' + totalProducts + ' products)'"></span>
                        </h1>
                        
                        <div class="flex items-center gap-4 text-sm">
                            <span class="font-medium text-gray-700">Sort By</span>
                            <select x-model="sortBy" @change="resetAndFetch()" class="border-gray-300 rounded text-sm">
                                <option value="latest">Newest First</option>
                                <option value="popular">Popularity</option>
                                <option value="price_low">Price -- Low to High</option>
                                <option value="price_high">Price -- High to Low</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" x-ref="productGrid">
                    <template x-for="product in products" :key="product.id + '-' + Math.random()">
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-lg transition-shadow duration-300 group">
                            <a :href="'/product/' + product.slug + (product.variant_id ? '?variant_id=' + product.variant_id : '')">
                                <div class="relative overflow-hidden aspect-square">
                                    <img :src="product.image" :alt="product.name" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         loading="lazy">
                                    <template x-if="product.discount > 0">
                                        <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded"
                                              x-text="product.discount + '% OFF'"></span>
                                    </template>
                                </div>
                                <div class="p-3">
                                    <template x-if="product.brand">
                                        <p class="text-xs text-gray-500 mb-1" x-text="product.brand"></p>
                                    </template>
                                    <h3 class="text-sm font-medium text-gray-900 line-clamp-2 mb-2 min-h-[2.5rem]" x-text="product.name"></h3>
                                    <template x-if="product.variant_label">
                                        <p class="text-xs text-gray-600 mb-1" x-text="product.variant_label"></p>
                                    </template>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-lg font-bold text-gray-900" x-text="'₹' + Number(product.price).toLocaleString('en-IN')"></span>
                                        <template x-if="product.original_price && product.original_price > product.price">
                                            <span class="text-sm text-gray-400 line-through" x-text="'₹' + Number(product.original_price).toLocaleString('en-IN')"></span>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="bg-green-600 text-white px-1.5 py-0.5 rounded flex items-center gap-1">
                                            <span x-text="product.rating"></span>
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </span>
                                        <span class="text-gray-500" x-text="'(' + product.reviews_count + ')'"></span>
                                    </div>
                                    <p class="text-xs text-green-600 mt-2" x-text="product.delivery_text"></p>
                                </div>
                            </a>
                        </div>
                    </template>
                </div>

                <!-- Loading Indicator -->
                <div x-show="loading" class="flex justify-center py-8">
                    <div class="flex items-center gap-3 text-gray-600">
                        <svg class="animate-spin h-6 w-6 text-xelnova-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="text-sm font-medium">Loading more products...</span>
                    </div>
                </div>

                <!-- Load More Sentinel (IntersectionObserver target) -->
                <div x-ref="loadMoreTrigger" class="h-4"></div>

                <!-- No More Products -->
                <div x-show="!hasMore && products.length > 0 && !loading" class="text-center py-6">
                    <p class="text-gray-500 text-sm">You've seen all <span x-text="totalProducts"></span> products</p>
                </div>

                <!-- No Products Found -->
                <div x-show="products.length === 0 && !loading" class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-gray-400 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                    <p class="text-gray-500 mb-4">Try adjusting your filters or search terms</p>
                    <a href="{{ route('marketplace.products') }}" class="inline-block bg-xelnova-green-500 text-white px-6 py-2 rounded-lg hover:bg-xelnova-green-600">Clear Filters</a>
                </div>

                <!-- Scroll to top button -->
                <button x-show="showScrollTop" 
                        @click="scrollToTop()"
                        x-transition
                        class="fixed bottom-8 right-8 bg-xelnova-green-600 text-white p-3 rounded-full shadow-lg hover:bg-xelnova-green-700 transition z-50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function infiniteProducts() {
    return {
        products: [],
        loading: false,
        currentPage: 0,
        hasMore: true,
        totalProducts: 0,
        sortBy: '{{ request('sort', 'latest') }}',
        showScrollTop: false,
        observer: null,
        
        // Get current filter params from URL
        getFilters() {
            const params = new URLSearchParams(window.location.search);
            const filters = {
                sort: this.sortBy,
                page: this.currentPage,
                per_page: 24
            };
            
            if (params.get('category')) filters.category = params.get('category');
            if (params.get('search')) filters.search = params.get('search');
            if (params.get('min_price')) filters.min_price = params.get('min_price');
            if (params.get('max_price')) filters.max_price = params.get('max_price');
            
            // Handle brand array
            const brands = params.getAll('brand[]');
            if (brands.length > 0) filters.brand = brands.join(',');
            
            return filters;
        },
        
        init() {
            // Initial fetch
            this.loadMore();
            
            // Setup IntersectionObserver for infinite scroll
            this.setupObserver();
            
            // Setup scroll listener for scroll-to-top button
            window.addEventListener('scroll', () => {
                this.showScrollTop = window.scrollY > 500;
            });
        },
        
        setupObserver() {
            const options = {
                root: null,
                rootMargin: '200px',
                threshold: 0
            };
            
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !this.loading && this.hasMore) {
                        this.loadMore();
                    }
                });
            }, options);
            
            // Start observing after a short delay to ensure element exists
            setTimeout(() => {
                if (this.$refs.loadMoreTrigger) {
                    this.observer.observe(this.$refs.loadMoreTrigger);
                }
            }, 100);
        },
        
        async loadMore() {
            if (this.loading || !this.hasMore) return;
            
            this.loading = true;
            this.currentPage++;
            
            try {
                const filters = this.getFilters();
                const queryString = new URLSearchParams(filters).toString();
                const response = await fetch(`/api/v1/products?${queryString}`);
                const data = await response.json();
                
                if (data.success) {
                    // Append new products
                    this.products = [...this.products, ...data.data];
                    this.hasMore = data.meta.has_more;
                    this.totalProducts = data.meta.total;
                    
                    console.log(`Loaded page ${this.currentPage}, total products: ${this.products.length}/${this.totalProducts}`);
                }
            } catch (error) {
                console.error('Error fetching products:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async resetAndFetch() {
            // Reset state
            this.currentPage = 0;
            this.products = [];
            this.hasMore = true;
            
            // Update URL with new sort option
            const url = new URL(window.location);
            url.searchParams.set('sort', this.sortBy);
            window.history.pushState({}, '', url);
            
            // Fetch first page
            await this.loadMore();
        },
        
        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };
}
</script>
</x-marketplace.layout>
