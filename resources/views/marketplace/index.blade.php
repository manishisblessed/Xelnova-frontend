<x-marketplace.layout>
    @section('title', 'Online Shopping Site for Mobiles, Electronics, Furniture, Grocery, Lifestyle, Books & More. Best Offers!')

    <!-- Hero Section -->
    <div class="bg-gray-100 py-2">
        <div class="container mx-auto px-2">
            <div class="relative bg-white rounded-lg shadow-sm overflow-hidden" x-data="{ 
                activeSlide: 0,
                slides: [
                    { image: '{{ asset("images/hero-electronics.png") }}', link: '#' },
                    { image: '{{ asset("images/hero-fashion.png") }}', link: '#' },
                    { image: '{{ asset("images/hero-home.png") }}', link: '#' }
                ],
                timer: null,
                init() {
                    this.timer = setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % this.slides.length;
                    }, 5000);
                }
            }">
                <!-- Slides -->
                <div class="relative h-40 md:h-72 overflow-hidden">
                    <template x-for="(slide, index) in slides" :key="index">
                        <a :href="slide.link" 
                           x-show="activeSlide === index"
                           x-transition:enter="transition ease-out duration-500"
                           x-transition:enter-start="opacity-0 transform scale-95"
                           x-transition:enter-end="opacity-100 transform scale-100"
                           x-transition:leave="transition ease-in duration-300"
                           x-transition:leave-start="opacity-100 transform scale-100"
                           x-transition:leave-end="opacity-0 transform scale-95"
                           class="absolute inset-0 w-full h-full">
                            <img :src="slide.image" alt="Banner" class="w-full h-full object-cover">
                        </a>
                    </template>
                </div>

                <!-- Controls -->
                <button @click="activeSlide = activeSlide === 0 ? slides.length - 1 : activeSlide - 1" 
                        class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white/50 hover:bg-white text-gray-800 p-2 rounded-r-lg shadow-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <button @click="activeSlide = (activeSlide + 1) % slides.length" 
                        class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white/50 hover:bg-white text-gray-800 p-2 rounded-l-lg shadow-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                <!-- Indicators -->
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                    <template x-for="(slide, index) in slides" :key="index">
                        <button @click="activeSlide = index" 
                                :class="{'bg-xelnova-green-500 w-6': activeSlide === index, 'bg-white/70 w-2': activeSlide !== index}"
                                class="h-2 rounded-full transition-all duration-300"></button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Categories -->
    <div class="bg-white py-6 shadow-sm mb-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                @foreach($categories as $category)
                    <x-marketplace.category-card 
                        href="{{ route('marketplace.products', ['category' => $category->slug]) }}" 
                        :category="['name' => $category->name, 'image' => $category->image_url]" 
                    />
                @endforeach
            </div>
        </div>
    </div>

    <!-- Flash Deals -->
    <div class="container mx-auto px-4 mb-4">
        <div class="bg-white p-4 rounded-lg shadow-sm flex flex-col md:flex-row gap-4">
            <!-- Deal Info -->
            <div class="md:w-1/5 flex flex-col justify-center items-center text-center p-4 bg-cover bg-center rounded-lg" style="background-image: url('{{ asset('images/flash-sale-bg.jpg') }}');">
                <h2 class="text-3xl font-bold text-white mb-2 drop-shadow-md">Best of Electronics</h2>
                <a href="{{ route('marketplace.products') }}" class="bg-white text-xelnova-green-600 px-4 py-2 rounded shadow-md font-medium hover:bg-gray-50 transition">View All</a>
            </div>

            <!-- Products Slider -->
            <div class="md:w-4/5 overflow-x-auto pb-4 scrollbar-hide">
                <div class="flex space-x-4 min-w-max">
                    @foreach($flashDeals as $product)
                        <div class="w-48">
                            <x-marketplace.product-card :product="[
                                'id' => $product->id,
                                'slug' => $product->slug,
                                'name' => $product->name,
                                'image' => $product->main_image_url,
                                'price' => $product->price,
                                'original_price' => $product->compare_at_price,
                                'discount' => $product->compare_at_price ? round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100) : 0,
                                'rating' => '4.' . rand(0, 9), // Placeholder - TODO: Implement ratings
                                'reviews_count' => rand(50, 500), // Placeholder - TODO: Implement reviews
                                'brand' => $product->brand?->name
                            ]" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Products Grid -->
    <div class="container mx-auto px-4 mb-8">
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Suggested for You</h2>
                <a href="{{ route('marketplace.products') }}" class="text-xelnova-green-600 font-medium hover:underline">View All</a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($featuredProducts as $product)
                    <x-marketplace.product-card :product="[
                        'id' => $product->id,
                        'slug' => $product->slug,
                        'name' => $product->name,
                        'image' => $product->main_image_url,
                        'price' => $product->price,
                        'original_price' => $product->compare_at_price,
                        'discount' => $product->compare_at_price ? round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100) : 0,
                        'rating' => '4.' . rand(0, 9), // Placeholder - TODO: Implement ratings
                        'reviews_count' => rand(50, 500) // Placeholder - TODO: Implement reviews
                    ]" />
                @endforeach
            </div>
        </div>
    </div>

    <!-- Promotional Banner -->
    <div class="container mx-auto px-4 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="#" class="block rounded-lg overflow-hidden shadow-sm hover:shadow-md transition h-48">
                <img src="{{ asset('images/promo-1.jpg') }}" alt="Promo 1" class="w-full h-full object-cover">
            </a>
            <a href="#" class="block rounded-lg overflow-hidden shadow-sm hover:shadow-md transition h-48">
                <img src="{{ asset('images/promo-2.jpg') }}" alt="Promo 2" class="w-full h-full object-cover">
            </a>
        </div>
    </div>

    <!-- Top Selection -->
    <div class="container mx-auto px-4 mb-12">
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Top Selection</h2>
                <a href="{{ route('marketplace.products') }}" class="text-xelnova-green-600 font-medium hover:underline">View All</a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border rounded-lg p-4 hover:shadow-md transition flex gap-4 items-center">
                    <div class="w-24 h-24 flex-shrink-0">
                        <img src="{{ asset('images/prod-5.jpg') }}" alt="Top Fashion" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Top Rated Fashion</h3>
                        <p class="text-sm text-green-600 mb-2">Min. 50% Off</p>
                        <a href="{{ route('marketplace.products') }}" class="text-xs bg-xelnova-green-500 text-white px-3 py-1 rounded">Shop Now</a>
                    </div>
                </div>
                <div class="border rounded-lg p-4 hover:shadow-md transition flex gap-4 items-center">
                    <div class="w-24 h-24 flex-shrink-0">
                        <img src="{{ asset('images/prod-3.jpg') }}" alt="Top Gadgets" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Best of Gadgets</h3>
                        <p class="text-sm text-green-600 mb-2">Up to 60% Off</p>
                        <a href="{{ route('marketplace.products') }}" class="text-xs bg-xelnova-green-500 text-white px-3 py-1 rounded">Shop Now</a>
                    </div>
                </div>
                <div class="border rounded-lg p-4 hover:shadow-md transition flex gap-4 items-center">
                    <div class="w-24 h-24 flex-shrink-0">
                        <img src="{{ asset('images/prod-7.jpg') }}" alt="Top Footwear" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Premium Footwear</h3>
                        <p class="text-sm text-green-600 mb-2">Min. 40% Off</p>
                        <a href="{{ route('marketplace.products') }}" class="text-xs bg-xelnova-green-500 text-white px-3 py-1 rounded">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-marketplace.layout>
