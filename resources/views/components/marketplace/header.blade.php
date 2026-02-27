@php
    // Fallback: Load categories if not provided by View Composer
    // Load with 2 levels of children for 3-level dropdown navigation
    if (!isset($navCategories)) {
        $navCategories = \App\Models\Category::with('children.children')
            ->active()
            ->topLevel()
            ->ordered()
            ->limit(10)
            ->get();
    }
@endphp

<header class="bg-white shadow-sm sticky top-0 z-40" x-data="{ mobileMenuOpen: false, searchOpen: false }">
    <!-- Top Bar -->
    <div class="bg-xelnova-green-500 text-white text-xs py-1">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <span><i class="fas fa-phone-alt mr-1"></i> Support: 1800-123-4567</span>
                <span class="hidden sm:inline">|</span>
                <span class="hidden sm:inline">Download App</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('seller.landing') }}" class="hover:text-xelnova-gold-300 transition">Sell on Xelnova</a>
                <span>|</span>
                <a href="#" class="hover:text-xelnova-gold-300 transition">Track Order</a>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between gap-4">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex-shrink-0">
                <img src="{{ asset('images/xelnova-logo.png') }}" alt="Xelnova" class="h-16 w-auto">
            </a>

            <!-- Search Bar (Desktop) -->
            <div class="hidden md:flex flex-1 max-w-2xl mx-4 relative" x-data="searchBar()">
                <div class="relative w-full">
                    <form @submit.prevent="performSearch" class="w-full">
                        <input type="text" 
                               x-model="query"
                               @input.debounce.300ms="fetchSuggestions"
                               @keydown="handleKeydown"
                               @focus="showDropdown = true"
                               @click.away="showDropdown = false"
                               placeholder="Search for products, brands and more" 
                               class="w-full bg-blue-50/30 border border-gray-200 rounded-lg py-2.5 pl-4 pr-12 focus:outline-none focus:border-xelnova-green-500 focus:ring-1 focus:ring-xelnova-green-500 transition-all text-sm">
                        <button type="submit" class="absolute right-0 top-0 h-full px-4 text-xelnova-green-600 hover:text-xelnova-green-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </button>
                    </form>

                    <!-- Autocomplete Dropdown -->
                    <div x-show="showDropdown && (products.length > 0 || categories.length > 0 || brands.length > 0)"
                         x-cloak
                         class="autocomplete-dropdown absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 max-h-96 overflow-y-auto z-50">
                        
                        <!-- Products -->
                        <template x-if="products.length > 0">
                            <div class="p-2">
                                <div class="text-xs font-semibold text-gray-500 uppercase px-3 py-2">Products</div>
                                <template x-for="(product, index) in products" :key="product.id">
                                    <a :href="product.url" 
                                       :class="isSelected('product', index) ? 'bg-blue-50 selected-item' : ''"
                                       class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded transition">
                                        <img :src="product.image" :alt="product.name" class="w-12 h-12 object-cover rounded">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900" x-text="product.name"></div>
                                            <div class="text-sm text-gray-600" x-text="product.price"></div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <!-- Categories -->
                        <template x-if="categories.length > 0">
                            <div class="p-2 border-t">
                                <div class="text-xs font-semibold text-gray-500 uppercase px-3 py-2">Categories</div>
                                <template x-for="(category, index) in categories" :key="category.id">
                                    <a :href="category.url" 
                                       :class="isSelected('category', index) ? 'bg-blue-50 selected-item' : ''"
                                       class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition"
                                       x-text="category.name"></a>
                                </template>
                            </div>
                        </template>

                        <!-- Brands -->
                        <template x-if="brands.length > 0">
                            <div class="p-2 border-t">
                                <div class="text-xs font-semibold text-gray-500 uppercase px-3 py-2">Brands</div>
                                <template x-for="(brand, index) in brands" :key="brand.id">
                                    <a :href="brand.url" 
                                       :class="isSelected('brand', index) ? 'bg-blue-50 selected-item' : ''"
                                       class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded transition"
                                       x-text="brand.name"></a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- User Actions -->
            <div class="flex items-center space-x-2 sm:space-x-6">
                @auth
                    <!-- User Dropdown (Logged In) -->
                    <div class="hidden sm:block relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                @click.away="open = false"
                                class="flex items-center gap-2 group hover:bg-blue-50 px-3 py-2 rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-700 group-hover:text-xelnova-green-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span class="font-medium text-gray-700 group-hover:text-xelnova-green-600">{{ Auth::user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                             x-cloak>
                            <div class="py-1">
                                <a href="{{ route('account.orders') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Orders</a>
                                <a href="{{ route('account.wishlist') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Wishlist</a>
                                <a href="{{ route('account.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Profile</a>
                                <hr class="my-1">
                                <form action="{{ route('customer.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Login Button (Not Logged In) -->
                    <a href="{{ route('customer.login') }}" class="hidden sm:flex items-center gap-2 group hover:bg-blue-50 px-3 py-2 rounded-lg transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-700 group-hover:text-xelnova-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span class="font-medium text-gray-700 group-hover:text-xelnova-green-600">Login</span>
                    </a>
                @endauth

                <!-- Cart -->
                <a href="{{ route('marketplace.cart') }}" class="flex items-center gap-2 group hover:bg-blue-50 px-3 py-2 rounded-lg transition relative"
                   x-data="headerCart()"
                   @cart-updated.window="cartCount = $event.detail.count">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-700 group-hover:text-xelnova-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                        <span x-show="cartCount > 0" 
                              x-text="cartCount > 9 ? '9+' : cartCount"
                              class="absolute -top-1 -right-1 bg-xelnova-gold-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"></span>
                    </div>
                    <span class="font-medium text-gray-700 group-hover:text-xelnova-green-600 hidden sm:block">Cart</span>
                </a>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Search -->
        <div class="mt-3 md:hidden" x-data="searchBar()">
            <form @submit.prevent="performSearch" class="relative w-full">
                <input type="text" 
                       x-model="query"
                       placeholder="Search for products..." 
                       class="w-full bg-gray-100 border-none rounded-lg py-2 pl-4 pr-10 focus:ring-1 focus:ring-xelnova-green-500 text-sm">
                <button type="submit" class="absolute right-0 top-0 h-full px-3 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Category Navigation (Desktop) - 3-Level Dropdown -->
    <div class="hidden md:block bg-white border-b border-gray-100" 
         x-data="{ activeDropdown: null, activeSubcategory: null }">
        <div class="container mx-auto px-4">
            <ul class="flex items-center justify-between gap-6 text-sm font-medium text-gray-700 py-3 max-w-screen-2xl mx-auto">
                @foreach($navCategories as $category)
                    <li class="relative"
                        @mouseenter="activeDropdown = {{ $category->id }}; activeSubcategory = {{ $category->children->first()?->id ?? 'null' }}"
                        @mouseleave="activeDropdown = null; activeSubcategory = null">
                        
                        <!-- Level 1: Main Category -->
                        <a href="{{ route('marketplace.products', ['category' => $category->slug]) }}"
                           class="flex items-center gap-1 hover:text-xelnova-green-600 cursor-pointer whitespace-nowrap py-2">
                            {{ $category->name }}
                            @if($category->children->count() > 0)
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            @endif
                        </a>
                        
                        <!-- Dropdown Panel (Two Columns) -->
                        @if($category->children->count() > 0)
                            <div x-show="activeDropdown === {{ $category->id }}"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 -translate-y-1"
                                 class="absolute top-full left-0 mt-0 bg-white shadow-xl border border-gray-200 rounded-b-lg z-[999]"
                                 style="min-width: 600px; max-width: 800px;"
                                 x-cloak>
                                
                                <div class="flex">
                                    <!-- Left Column: Level 2 Subcategories -->
                                    <div class="bg-gray-50 border-r border-gray-200 py-4" style="min-width: 250px; max-width: 300px;">
                                        @foreach($category->children as $subcategory)
                                            <a href="{{ route('marketplace.products', ['category' => $subcategory->slug]) }}"
                                               @mouseenter="activeSubcategory = {{ $subcategory->id }}"
                                               class="block px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-xelnova-green-600 transition-all"
                                               :class="{ 'bg-white border-l-3 border-xelnova-green-500 text-xelnova-green-600': activeSubcategory === {{ $subcategory->id }} }">
                                                <span class="flex items-center justify-between">
                                                    <span>{{ $subcategory->name }}</span>
                                                    @if($subcategory->children->count() > 0)
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                                        </svg>
                                                    @endif
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Right Column: Level 3 Categories (Dynamic) -->
                                    <div class="bg-white py-4 px-6 flex-1" style="min-width: 300px;">
                                        @foreach($category->children as $subcategory)
                                            <div x-show="activeSubcategory === {{ $subcategory->id }}"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0"
                                                 x-transition:enter-end="opacity-100"
                                                 x-cloak>
                                                
                                                @if($subcategory->children->count() > 0)
                                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                                                        {{ $subcategory->name }}
                                                    </h4>
                                                    <div class="space-y-1">
                                                        @foreach($subcategory->children as $childCategory)
                                                            <a href="{{ route('marketplace.products', ['category' => $childCategory->slug]) }}"
                                                               class="block text-sm text-gray-700 hover:text-xelnova-green-600 py-1.5 transition-colors">
                                                                {{ $childCategory->name }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-sm text-gray-500 italic">
                                                        <a href="{{ route('marketplace.products', ['category' => $subcategory->slug]) }}"
                                                           class="text-xelnova-green-600 hover:underline">
                                                            View all {{ $subcategory->name }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-x-full"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-full"
         class="fixed inset-0 z-50 bg-white md:hidden" 
         x-cloak>
        <div class="flex flex-col h-full">
            <div class="bg-xelnova-green-600 p-4 flex justify-between items-center text-white">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <span class="font-bold text-lg">Login & Signup</span>
                </div>
                <button @click="mobileMenuOpen = false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <ul class="space-y-4 text-gray-700 font-medium">
                    <li class="border-b pb-2"><a href="{{ route('marketplace.products') }}">All Categories</a></li>
                    <li class="border-b pb-2"><a href="{{ route('account.orders') }}">My Orders</a></li>
                    <li class="border-b pb-2"><a href="{{ route('marketplace.cart') }}">My Cart</a></li>
                    <li class="border-b pb-2"><a href="{{ route('account.wishlist') }}">My Wishlist</a></li>
                    <li class="border-b pb-2"><a href="{{ route('account.profile') }}">My Account</a></li>
                    <li class="border-b pb-2"><a href="#">Notifications</a></li>
                    <li class="border-b pb-2"><a href="{{ route('contact') }}">Help Centre</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<script>
    function headerCart() {
        return {
            cartCount: 0,
            async init() {
                await this.fetchCartCount();
            },
            async fetchCartCount() {
                try {
                    const response = await fetch('/api/v1/cart/count', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.cartCount = data.count;
                    }
                } catch (error) {
                    console.error('Error fetching cart count:', error);
                }
            }
        };
    }

    function searchBar() {
        return {
            query: '',
            showDropdown: false,
            products: [],
            categories: [],
            brands: [],
            selectedIndex: -1,

            get allResults() {
                // Flatten all results into a single array for navigation
                const results = [];
                this.products.forEach(p => results.push({ type: 'product', data: p }));
                this.categories.forEach(c => results.push({ type: 'category', data: c }));
                this.brands.forEach(b => results.push({ type: 'brand', data: b }));
                return results;
            },

            async fetchSuggestions() {
                if (this.query.length < 2) {
                    this.products = [];
                    this.categories = [];
                    this.brands = [];
                    this.selectedIndex = -1;
                    return;
                }

                try {
                    const response = await fetch(`/api/v1/search/autocomplete?q=${encodeURIComponent(this.query)}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        this.products = data.data.products || [];
                        this.categories = data.data.categories || [];
                        this.brands = data.data.brands || [];
                        this.selectedIndex = -1;
                    }
                } catch (error) {
                    console.error('Error fetching search suggestions:', error);
                }
            },

            handleKeydown(event) {
                const results = this.allResults;
                
                if (!this.showDropdown || results.length === 0) {
                    return;
                }

                switch(event.key) {
                    case 'ArrowDown':
                        event.preventDefault();
                        this.selectedIndex = Math.min(this.selectedIndex + 1, results.length - 1);
                        this.scrollToSelected();
                        break;
                    
                    case 'ArrowUp':
                        event.preventDefault();
                        this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                        this.scrollToSelected();
                        break;
                    
                    case 'Enter':
                        event.preventDefault();
                        if (this.selectedIndex >= 0 && this.selectedIndex < results.length) {
                            // Navigate to selected item
                            const selected = results[this.selectedIndex];
                            window.location.href = selected.data.url;
                        } else {
                            // Perform search
                            this.performSearch();
                        }
                        break;
                    
                    case 'Escape':
                        this.showDropdown = false;
                        this.selectedIndex = -1;
                        break;
                }
            },

            scrollToSelected() {
                this.$nextTick(() => {
                    const dropdown = this.$el.querySelector('.autocomplete-dropdown');
                    const selected = dropdown?.querySelector('.selected-item');
                    if (selected && dropdown) {
                        const dropdownRect = dropdown.getBoundingClientRect();
                        const selectedRect = selected.getBoundingClientRect();
                        
                        if (selectedRect.bottom > dropdownRect.bottom) {
                            selected.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                        } else if (selectedRect.top < dropdownRect.top) {
                            selected.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                        }
                    }
                });
            },

            isSelected(type, index) {
                const results = this.allResults;
                let currentIndex = 0;
                
                for (let i = 0; i < results.length; i++) {
                    if (results[i].type === type) {
                        if (type === 'product' && currentIndex === index) {
                            return i === this.selectedIndex;
                        } else if (type === 'category' && currentIndex === index) {
                            return i === this.selectedIndex;
                        } else if (type === 'brand' && currentIndex === index) {
                            return i === this.selectedIndex;
                        }
                        currentIndex++;
                    }
                }
                return false;
            },

            performSearch() {
                if (this.query.trim()) {
                    window.location.href = `/products?search=${encodeURIComponent(this.query.trim())}`;
                }
            }
        };
    }
</script>
