<x-marketplace.layout>
    @section('title', 'Product Detail')

    <div class="bg-gray-100 py-4">
        <div class="container mx-auto px-4">
            <!-- Breadcrumbs -->
            <div class="text-xs text-gray-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-xelnova-green-600">Home</a>
                <span class="mx-1">›</span>
                
                @php
                    // Build breadcrumb trail from category hierarchy
                    $breadcrumbs = [];
                    $currentCategory = $product->category;
                    while ($currentCategory) {
                        array_unshift($breadcrumbs, $currentCategory);
                        $currentCategory = $currentCategory->parent;
                    }
                @endphp
                
                @foreach($breadcrumbs as $category)
                    <a href="{{ route('marketplace.products', ['category' => $category->slug]) }}" class="hover:text-xelnova-green-600">{{ $category->name }}</a>
                    <span class="mx-1">›</span>
                @endforeach
                
                <span class="text-gray-700 font-medium">{{ $product->name }}</span>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 lg:p-6 mb-4">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Image Gallery -->
                    @php
                        $mainImage = $product->main_image_url ?? 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&q=80';
                        $allImages = collect([$mainImage]);
                        if ($product->images && $product->images->count() > 0) {
                            foreach ($product->images as $img) {
                                $allImages->push($img->image_url);
                            }
                        }
                    @endphp
                    
                    <div class="w-full lg:w-1/3 flex-shrink-0" 
                         x-data="productActions({ productId: {{ $product->id }}, productName: '{{ addslashes($product->name) }}', mainImage: '{{ $mainImage }}', hasVariants: {{ $product->has_variants ? 'true' : 'false' }}, inStock: {{ $product->quantity > 0 && $product->is_active ? 'true' : 'false' }} })">
                        <div class="border border-gray-200 rounded-lg overflow-hidden mb-4 relative group">
                            <img :src="activeImage" alt="{{ $product->name }}" class="w-full h-auto object-contain max-h-[500px]">
                            <button class="absolute top-4 right-4 p-2 rounded-full bg-white shadow-md text-gray-400 hover:text-red-500 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex gap-2 overflow-x-auto pb-2">
                            @foreach($allImages as $image)
                                <button @click="activeImage = '{{ $image }}'" 
                                        class="w-16 h-16 border rounded-md overflow-hidden hover:border-xelnova-green-500 focus:ring-2 focus:ring-xelnova-green-500 flex-shrink-0"
                                        :class="{ 'border-xelnova-green-500 ring-2 ring-xelnova-green-500': activeImage === '{{ $image }}' }">
                                    <img src="{{ $image }}" class="w-full h-full object-cover" alt="Product thumbnail">
                                </button>
                            @endforeach
                        </div>
                        
                        <!-- Quantity Selector -->
                        <div class="flex items-center gap-4 mt-4 mb-4">
                            <span class="text-sm text-gray-600 font-medium">Quantity:</span>
                            <div class="flex items-center gap-2">
                                <button @click="quantity > 1 ? quantity-- : null" 
                                        :disabled="quantity <= 1"
                                        class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    -
                                </button>
                                <input type="text" x-model="quantity" readonly class="w-12 text-center border border-gray-300 rounded py-1 text-sm bg-gray-50">
                                <button @click="quantity < 10 ? quantity++ : null" 
                                        :disabled="quantity >= 10"
                                        class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    +
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex gap-4">
                            <button @click="addToCart()" 
                                    :disabled="addingToCart || !canAddToCart()"
                                    class="flex-1 bg-xelnova-gold-500 hover:bg-xelnova-gold-600 text-white font-bold py-3 rounded-lg shadow-sm transition uppercase text-sm flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                                <svg x-show="addingToCart" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <svg x-show="!addingToCart" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                </svg>
                                <span x-text="addingToCart ? 'Adding...' : 'Add to Cart'"></span>
                            </button>
                            <button @click="buyNow()" 
                                    :disabled="addingToCart || !canAddToCart()"
                                    class="flex-1 bg-xelnova-green-600 hover:bg-xelnova-green-700 text-white font-bold py-3 rounded-lg shadow-sm transition uppercase text-sm flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                                </svg>
                                Buy Now
                            </button>
                        </div>
                        
                        <!-- Success/Error Message -->
                        <div x-show="message" 
                             x-transition
                             :class="messageType === 'success' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300'"
                             class="mt-4 p-3 rounded-lg border text-sm flex items-center gap-2">
                            <svg x-show="messageType === 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <svg x-show="messageType === 'error'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            <span x-text="message"></span>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="flex-1" x-data="productVariants()">
                        <h1 class="text-xl md:text-2xl font-medium text-gray-900 mb-2">{{ $product->name }}</h1>
                        
                        <div class="flex items-center gap-4 mb-4">
                            {{-- Placeholder: Ratings & Reviews (to be implemented) --}}
                            <div class="flex items-center bg-green-600 text-white text-xs font-bold px-2 py-1 rounded gap-1">
                                <span>{{ '4.' . rand(0, 9) }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3">
                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-sm text-gray-500 font-medium">{{ rand(50, 500) }} Ratings & {{ rand(10, 100) }} Reviews</span>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-baseline gap-3">
                                <span class="text-3xl font-bold text-gray-900" x-text="getFormattedPrice()">₹{{ number_format($product->price, 0) }}</span>
                                <template x-if="selectedVariant && selectedVariant.compare_at_price">
                                    <span class="text-lg text-gray-500 line-through" x-text="'₹' + selectedVariant.compare_at_price.toLocaleString('en-IN')"></span>
                                </template>
                                @if(!$product->has_variants && $product->compare_at_price && $product->compare_at_price > $product->price)
                                    <span class="text-lg text-gray-500 line-through">₹{{ number_format($product->compare_at_price, 0) }}</span>
                                    <span class="text-green-600 font-bold text-lg">{{ $product->discount_percent }}% off</span>
                                @endif
                            </div>
                            <div class="mt-2">
                                <template x-if="isInStock()">
                                    <span class="text-green-600 font-medium text-sm">✓ In Stock</span>
                                </template>
                                <template x-if="!isInStock()">
                                    <span class="text-red-600 font-medium text-sm">Out of Stock</span>
                                </template>
                                <template x-if="isInStock() && selectedVariant && selectedVariant.quantity < 10">
                                    <span class="text-orange-600 text-sm ml-2">Only <span x-text="selectedVariant.quantity"></span> left!</span>
                                </template>
                                @if(!$product->has_variants && $product->quantity > 0 && $product->quantity < 10)
                                    <span class="text-orange-600 text-sm ml-2">Only {{ $product->quantity }} left!</span>
                                @endif
                            </div>
                        </div>

                        <!-- Offers 
                        <div class="mb-6">
                            <h3 class="text-sm font-bold text-gray-800 mb-2">Available Offers</h3>
                            <ul class="text-sm space-y-2">
                                <li class="flex items-start gap-2">
                                    <img src="https://rukminim1.flixcart.com/www/36/36/promos/06/09/2016/c22c9fc4-0555-4460-8401-bf5c28d7ba29.png?q=90" class="w-4 h-4 mt-0.5">
                                    <span><span class="font-medium">Bank Offer</span> 5% Unlimited Cashback on Flipkart Axis Bank Credit Card <a href="#" class="text-blue-600 font-medium">T&C</a></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <img src="https://rukminim1.flixcart.com/www/36/36/promos/06/09/2016/c22c9fc4-0555-4460-8401-bf5c28d7ba29.png?q=90" class="w-4 h-4 mt-0.5">
                                    <span><span class="font-medium">Bank Offer</span> 10% off on HDFC Bank Credit Card EMI Transactions, up to ₹1,500 <a href="#" class="text-blue-600 font-medium">T&C</a></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <img src="https://rukminim1.flixcart.com/www/36/36/promos/06/09/2016/c22c9fc4-0555-4460-8401-bf5c28d7ba29.png?q=90" class="w-4 h-4 mt-0.5">
                                    <span><span class="font-medium">Special Price</span> Get extra ₹5000 off (price inclusive of cashback/coupon) <a href="#" class="text-blue-600 font-medium">T&C</a></span>
                                </li>
                            </ul>
                        </div>
                        -->

                        <!-- Variants -->
                        <template x-if="variantTypes.length > 0">
                            <div class="space-y-4 mb-6">
                                <template x-for="variantType in variantTypes" :key="variantType.id">
                                    <div>
                                        <h3 class="text-sm text-gray-700 font-medium mb-2" x-text="variantType.name"></h3>
                                        
                                        <!-- Color Swatches -->
                                        <template x-if="variantType.input_type === 'color'">
                                            <div class="flex gap-3 flex-wrap">
                                                <template x-for="option in variantType.options" :key="option.id">
                                                    <div class="flex flex-col items-center gap-1">
                                                        <button 
                                                            type="button"
                                                            @click="selectOption(variantType.id, option.id)"
                                                            :class="selectedOptions[variantType.id] === option.id ? 'ring-2 ring-xelnova-green-600 ring-offset-2' : 'ring-1 ring-gray-300'"
                                                            :disabled="!isOptionAvailable(variantType.id, option.id)"
                                                            class="w-12 h-12 rounded-full relative disabled:opacity-40 disabled:cursor-not-allowed transition"
                                                            :style="'background-color: ' + (option.color_code || '#ccc')"
                                                            :title="option.display_value">
                                                            <template x-if="selectedOptions[variantType.id] === option.id">
                                                                <svg class="w-5 h-5 text-white absolute inset-0 m-auto drop-shadow" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                </svg>
                                                            </template>
                                                        </button>
                                                        <span class="text-xs text-gray-600 text-center" x-text="option.display_value"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        
                                        <!-- Button Pills (for size, storage, etc) -->
                                        <template x-if="variantType.input_type !== 'color'">
                                            <div class="flex gap-2 flex-wrap">
                                                <template x-for="option in variantType.options" :key="option.id">
                                                    <button 
                                                        type="button"
                                                        @click="selectOption(variantType.id, option.id)"
                                                        :class="selectedOptions[variantType.id] === option.id ? 'bg-xelnova-green-600 text-white border-xelnova-green-600' : 'bg-white text-gray-700 border-gray-300 hover:border-xelnova-green-600'"
                                                        :disabled="!isOptionAvailable(variantType.id, option.id)"
                                                        class="px-4 py-2 border rounded-md text-sm font-medium disabled:opacity-40 disabled:cursor-not-allowed transition"
                                                        x-text="option.display_value">
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Seller Info -->
                        <div class="flex items-start gap-4 mb-6 border-t pt-4">
                            <div class="flex-1">
                                <h3 class="text-sm text-gray-500 font-medium mb-1">Seller</h3>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium text-blue-600">{{ $product->seller->seller->business_name ?? $product->seller->name ?? 'Unknown Seller' }}</span>
                                    {{-- Placeholder: Seller rating (to be implemented) --}}
                                    <span class="bg-blue-600 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ '4.' . rand(5, 9) }} ★</span>
                                </div>
                                {{-- Placeholder: Seller policies (to be implemented) --}}
                                <ul class="text-xs text-gray-500 list-disc list-inside">
                                    <li>7 Days Replacement Policy</li>
                                    <li>GST invoice available</li>
                                </ul>
                            </div>
                            <div class="flex-1" x-data="deliveryEstimation({{ $product->id }}, '{{ $userPincode ?? '' }}')">
                                <h3 class="text-sm text-gray-500 font-medium mb-1">Delivery</h3>
                                <div class="flex items-center gap-2 mb-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    
                                    <div x-show="!isEditing && pincode" class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900">Deliver to <span class="font-bold" x-text="pincode"></span></span>
                                        <button @click="startEditing" class="text-blue-600 text-xs font-medium hover:underline">Change</button>
                                    </div>

                                    <div x-show="!isEditing && !pincode" class="flex items-center gap-2">
                                        <span class="text-sm text-gray-500">Enter pincode for delivery estimate</span>
                                        <button @click="startEditing" class="text-blue-600 text-xs font-medium hover:underline">Check</button>
                                    </div>

                                    <div x-show="isEditing" class="flex items-center gap-2" x-cloak>
                                        <input type="text" x-model="tempPincode" 
                                               @keydown.enter="savePincode"
                                               class="w-24 text-sm border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500 py-1 h-8" 
                                               placeholder="Pincode" maxlength="6">
                                        <button @click="savePincode" class="text-xs bg-xelnova-green-600 text-white px-2 py-1 rounded hover:bg-xelnova-green-700">Check</button>
                                        <button @click="cancelEditing" class="text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                                    </div>
                                </div>

                                <div x-show="loading" class="text-sm text-gray-500 flex items-center gap-1">
                                    <svg class="animate-spin h-3 w-3 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Calculating...
                                </div>

                                <div x-show="!loading && estimate" x-transition>
                                    <p class="text-sm font-medium text-green-600" x-text="estimate?.message"></p>
                                    <p x-show="estimate?.delivery_date" class="text-xs text-gray-500">Estimated between <span x-text="estimate?.delivery_date"></span></p>
                                </div>

                                <div x-show="!loading && error" class="text-sm text-red-500" x-text="error"></div>
                            </div>
                        </div>

                        <!-- Highlights -->
                        @if(!empty($product->highlights) && is_array($product->highlights) && count($product->highlights) > 0)
                            <div class="mb-6 border rounded-lg p-4 bg-gray-50">
                                <h3 class="text-sm font-bold text-gray-800 mb-2">Highlights</h3>
                                <ul class="text-sm text-gray-700 list-disc list-inside space-y-1">
                                    @foreach($product->highlights as $highlight)
                                        @if(!empty($highlight))
                                            <li>{{ $highlight }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Description -->
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-bold text-gray-800 mb-2">Product Description</h2>
                            <div class="text-sm text-gray-600 leading-relaxed">
                                @if($product->description)
                                    {!! nl2br(e($product->description)) !!}
                                @elseif($product->short_description)
                                    {!! nl2br(e($product->short_description)) !!}
                                @else
                                    <p class="text-gray-400 italic">No description available for this product.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews -->
            <div class="bg-white rounded-lg shadow-sm p-4 lg:p-6 mb-4" x-data="reviewsSection({{ $product->id }})">
                <div class="flex justify-between items-center mb-4 border-b pb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Ratings & Reviews</h2>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= floor($product->average_rating) ? 'text-yellow-400' : 'text-gray-300' }} fill-current" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm text-gray-600">{{ number_format($product->average_rating, 1) }} out of 5 ({{ $product->reviews_count }} reviews)</span>
                        </div>
                    </div>
                    <button @click="showReviewForm = !showReviewForm" 
                            x-show="canReview"
                            class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded shadow-sm hover:bg-gray-50 font-medium text-sm">
                        <span x-show="!showReviewForm">Write a Review</span>
                        <span x-show="showReviewForm">Cancel</span>
                    </button>
                </div>

                <!-- Review Form -->
                <div x-show="showReviewForm" x-cloak class="mb-6 p-4 border border-gray-200 rounded-lg">
                    <form @submit.prevent="submitReview">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your Rating</label>
                            <div class="flex gap-2">
                                <template x-for="star in 5" :key="star">
                                    <button type="button" @click="newReview.rating = star" class="focus:outline-none">
                                        <svg class="w-8 h-8 fill-current transition" 
                                             :class="star <= newReview.rating ? 'text-yellow-400' : 'text-gray-300'"
                                             viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Review Title (Optional)</label>
                            <input type="text" x-model="newReview.title" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Summarize your experience">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                            <textarea x-model="newReview.comment" rows="4" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Share your thoughts about this product"></textarea>
                        </div>
                        <div x-show="error" class="mb-4 bg-red-50 text-red-600 p-3 rounded text-sm" x-text="error"></div>
                        <button type="submit" :disabled="submitting || !newReview.rating"
                                class="bg-xelnova-green-600 hover:bg-xelnova-green-700 text-white font-bold py-2 px-6 rounded shadow-sm transition disabled:opacity-70">
                            <span x-show="!submitting">Submit Review</span>
                            <span x-show="submitting">Submitting...</span>
                        </button>
                    </form>
                </div>
                
                <!-- Reviews List -->
                <div class="space-y-6">
                    <template x-if="reviews.length === 0 && !loading">
                        <p class="text-gray-500 text-center py-8">No reviews yet. Be the first to review this product!</p>
                    </template>

                    <template x-for="review in reviews" :key="review.id">
                        <div class="border-b pb-4 last:border-0 last:pb-0">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded flex items-center gap-1">
                                    <span x-text="review.rating"></span>
                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </div>
                                <span class="font-bold text-gray-800 text-sm" x-text="review.title || 'Great Product'"></span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2" x-text="review.comment"></p>
                            <div class="flex items-center gap-4 text-xs text-gray-400">
                                <span class="font-medium text-gray-500" x-text="review.user?.name || 'Anonymous'"></span>
                                <span x-show="review.is_verified_purchase">
                                    <svg class="w-3 h-3 inline text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Verified Purchase
                                </span>
                                <span x-text="new Date(review.created_at).toLocaleDateString()"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="loading" class="text-center py-4">
                        <svg class="animate-spin h-8 w-8 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div class="bg-white rounded-lg shadow-sm p-4 lg:p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Similar Products</h2>
                @if($relatedProducts && $relatedProducts->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($relatedProducts as $relatedProduct)
                            <x-marketplace.product-card :product="[
                                'id' => $relatedProduct->id,
                                'name' => $relatedProduct->name,
                                'image' => $relatedProduct->main_image_url,
                                'price' => $relatedProduct->price,
                                'compare_at_price' => $relatedProduct->compare_at_price,
                                'rating' => '4.' . rand(0, 9),
                                'reviews_count' => rand(50, 500)
                            ]" />
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No similar products found.</p>
                @endif
        </div>
    </div>

    <script>
        function productActions(config) {
            return {
                productId: config.productId,
                productName: config.productName,
                activeImage: config.mainImage || '',
                quantity: 1,
                addingToCart: false,
                message: '',
                messageType: 'success',
                hasVariants: config.hasVariants || false,
                inStock: config.inStock || false,
                variantStock: false,
                variantSelected: false,
                selectedVariantId: null,
                
                init() {
                    window.addEventListener('variant-changed', (e) => {
                        this.variantStock = e.detail.inStock;
                        this.variantSelected = !!e.detail.variant;
                        if (e.detail.variant) {
                            this.selectedVariantId = e.detail.variant.id;
                        } else {
                            this.selectedVariantId = null;
                        }
                        
                        // Update image if variant has one
                        if (e.detail.variant && e.detail.variant.main_image_url) {
                            // Check if image is present
                            if (e.detail.variant.main_image_url) {
                                this.activeImage = e.detail.variant.main_image_url;
                            }
                        }
                    });
                },
                
                canAddToCart() {
                    if (!this.hasVariants) {
                        return this.inStock;
                    }
                    return this.variantSelected && this.variantStock;
                },

                async addToCart() {
                    if (this.addingToCart) return;
                    
                    // Check if variant is selected when product has variants
                    if (this.hasVariants) {
                        if (!this.variantSelected) {
                            this.message = 'Please select product options';
                            this.messageType = 'error';
                            return;
                        }
                        if (!this.variantStock) {
                            this.message = 'Selected variant is out of stock';
                            this.messageType = 'error';
                            return;
                        }
                    }
                    
                    this.addingToCart = true;
                    this.message = '';

                    try {
                        const requestBody = {
                            product_id: this.productId,
                            quantity: this.quantity
                        };
                        
                        // Add variant_id if product has variants
                        if (this.hasVariants && this.selectedVariantId) {
                            requestBody.variant_id = this.selectedVariantId;
                        }
                        
                        const response = await fetch('/api/v1/cart/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(requestBody)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.message = `Added ${this.quantity} item(s) to cart`;
                            this.messageType = 'success';
                            
                            // Update header cart count
                            window.dispatchEvent(new CustomEvent('cart-updated', {
                                detail: { count: data.data.count }
                            }));

                            // Auto-hide message after 3 seconds
                            setTimeout(() => {
                                this.message = '';
                            }, 3000);
                        } else {
                            this.message = data.message || 'Failed to add to cart';
                            this.messageType = 'error';
                        }
                    } catch (error) {
                        console.error('Error adding to cart:', error);
                        this.message = 'Failed to add to cart. Please try again.';
                        this.messageType = 'error';
                    } finally {
                        this.addingToCart = false;
                    }
                },

                async buyNow() {
                    // Add to cart first, then redirect to checkout
                    await this.addToCart();
                    if (this.messageType === 'success') {
                        window.location.href = '/checkout';
                    }
                },
                
                hasAnyStock() {
                    // For products with variants, check if any variant has stock
                    if (!this.hasVariants) return true;
                    
                    const variantComponent = this.$root.querySelector('[x-data*="productVariants"]')?.__x?.$data;
                    if (!variantComponent || !variantComponent.variants) return false;
                    
                    return variantComponent.variants.some(v => v.is_active && v.quantity > 0);
                }
            };
        }
        
        function productVariants() {
            return {
                variantTypes: @json($variantTypes ?? []),
                variants: @json($variants ?? []),
                selectedOptions: {},
                selectedVariant: @json($defaultVariant),
                
                init() {
                    // Initialize selected options from default variant
                    if (this.selectedVariant && this.selectedVariant.option_ids) {
                        // Map each option_id to its variant_type_id
                        this.variantTypes.forEach(vType => {
                            const matchingOption = vType.options.find(opt => 
                                this.selectedVariant.option_ids.includes(opt.id)
                            );
                            if (matchingOption) {
                                this.selectedOptions[vType.id] = matchingOption.id;
                            }
                        });
                    }
                    
                    // Dispatch initial variant state
                    this.$nextTick(() => {
                        this.notifyVariantChange();
                    });
                },
                
                selectOption(variantTypeId, optionId) {
                    this.selectedOptions[variantTypeId] = optionId;
                    this.updateSelectedVariant();
                },
                
                updateSelectedVariant() {
                    // Find variant matching all selected options
                    const selectedOptionIds = Object.values(this.selectedOptions);
                    
                    if (selectedOptionIds.length !== this.variantTypes.length) {
                        // Not all variant types selected yet
                        return;
                    }
                    
                    const variant = this.variants.find(v => {
                        return selectedOptionIds.every(optionId => 
                            v.option_ids.includes(optionId)
                        );
                    });
                    
                    if (variant) {
                        this.selectedVariant = variant;
                        this.notifyVariantChange();
                    }
                },
                
                notifyVariantChange() {
                    window.dispatchEvent(new CustomEvent('variant-changed', {
                        detail: { 
                            variant: this.selectedVariant,
                            inStock: this.isInStock()
                        }
                    }));
                },
                
                isOptionAvailable(variantTypeId, optionId) {
                    // Check if any variant with this option is available
                    // considering other selected options
                    const otherSelectedOptions = Object.entries(this.selectedOptions)
                        .filter(([typeId]) => parseInt(typeId) !== variantTypeId)
                        .map(([, optId]) => optId);
                    
                    return this.variants.some(v => {
                        const hasThisOption = v.option_ids.includes(optionId);
                        const hasOtherOptions = otherSelectedOptions.every(optId => 
                            v.option_ids.includes(optId)
                        );
                        const isAvailable = v.is_active && v.quantity > 0;
                        return hasThisOption && hasOtherOptions && isAvailable;
                    });
                },
                
                getFormattedPrice() {
                    // For products with variants, use selected variant price
                    if (this.selectedVariant) {
                        return '₹' + this.selectedVariant.price.toLocaleString('en-IN');
                    }
                    // For products without variants, use base product price
                    const basePrice = {{ $product->price ?? 0 }};
                    return '₹' + basePrice.toLocaleString('en-IN');
                },
                
                isInStock() {
                    // For products with variants, check selected variant stock
                    if (this.selectedVariant) {
                        return this.selectedVariant.quantity > 0 && this.selectedVariant.is_active;
                    }
                    // For products without variants, check base product stock
                    const baseQuantity = {{ $product->quantity ?? 0 }};
                    const baseIsActive = {{ $product->is_active ? 'true' : 'false' }};
                    return baseQuantity > 0 && baseIsActive;
                }
            };
        }

        function reviewsSection(productId) {
            return {
                productId: productId,
                reviews: [],
                canReview: false,
                showReviewForm: false,
                loading: true,
                submitting: false,
                error: '',
                newReview: {
                    rating: 0,
                    title: '',
                    comment: ''
                },

                async init() {
                    await this.loadReviews();
                    await this.checkCanReview();
                },

                async loadReviews() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/v1/reviews/product/${this.productId}`);
                        const data = await response.json();
                        if (data.success) {
                            this.reviews = data.data.data || [];
                        }
                    } catch (error) {
                        console.error('Error loading reviews:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async checkCanReview() {
                    try {
                        const response = await fetch(`/api/v1/reviews/can-review/${this.productId}`);
                        const data = await response.json();
                        if (data.success) {
                            this.canReview = data.can_review;
                        }
                    } catch (error) {
                        console.error('Error checking review eligibility:', error);
                    }
                },

                async submitReview() {
                    if (!this.newReview.rating || !this.newReview.comment) {
                        this.error = 'Please provide a rating and comment';
                        return;
                    }

                    this.submitting = true;
                    this.error = '';

                    try {
                        const response = await fetch('/api/v1/reviews', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                product_id: this.productId,
                                rating: this.newReview.rating,
                                title: this.newReview.title,
                                comment: this.newReview.comment
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Reset form
                            this.newReview = { rating: 0, title: '', comment: '' };
                            this.showReviewForm = false;
                            this.canReview = false;
                            
                            // Reload reviews
                            await this.loadReviews();
                            
                            // Reload page to update average rating
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            this.error = data.message || 'Failed to submit review';
                        }
                    } catch (error) {
                        console.error('Error submitting review:', error);
                        this.error = 'Failed to submit review. Please try again.';
                    } finally {
                        this.submitting = false;
                    }
                }
            };
        }
        function deliveryEstimation(productId, defaultPincode = '') {
            return {
                productId: productId,
                pincode: '',
                tempPincode: '',
                estimate: null,
                loading: false,
                error: '',
                isEditing: false,

                init() {
                    // Check for invalid pincode (length != 6)
                    const savedPincode = this.getCookie('delivery_pincode');
                    
                    if (savedPincode && savedPincode.length === 6) {
                        this.pincode = savedPincode;
                        this.tempPincode = savedPincode;
                        this.checkEstimate();
                    } else if (defaultPincode && defaultPincode.length === 6) {
                        this.pincode = defaultPincode;
                        this.tempPincode = defaultPincode;
                        this.checkEstimate();
                    } else {
                        this.isEditing = true;
                    }
                },

                startEditing() {
                    this.isEditing = true;
                    this.tempPincode = this.pincode;
                    this.$nextTick(() => {
                        this.$root.querySelector('input[placeholder="Pincode"]')?.focus();
                    });
                },

                cancelEditing() {
                    if (this.pincode) {
                        this.isEditing = false;
                        this.tempPincode = this.pincode;
                    } else {
                        // If no pincode set, keep editing but maybe clear error
                        this.error = '';
                    }
                },

                async savePincode() {
                    if (!this.tempPincode || this.tempPincode.length !== 6 || isNaN(this.tempPincode)) {
                        this.error = 'Please enter a valid 6-digit pincode';
                        return;
                    }

                    this.pincode = this.tempPincode;
                    this.isEditing = false;
                    this.setCookie('delivery_pincode', this.pincode, 30); // Save for 30 days
                    await this.checkEstimate();
                },

                async checkEstimate() {
                    this.loading = true;
                    this.error = '';
                    console.log('Checking estimate for pincode:', this.pincode, 'Product:', this.productId);
                    try {
                        const url = `/api/v1/delivery/estimate?product_id=${this.productId}&pincode=${this.pincode}`;
                        console.log('Fetching:', url);
                        
                        const response = await fetch(url);
                        console.log('Response status:', response.status);
                        
                        const data = await response.json();
                        console.log('Response data:', data);

                        if (data.success) {
                            this.estimate = data.data;
                        } else {
                            this.error = data.message || 'Could not calculate delivery date';
                            this.estimate = null;
                        }
                    } catch (e) {
                        this.error = 'Failed to fetch delivery estimate';
                        console.error('Estimate error:', e);
                    } finally {
                        this.loading = false;
                    }
                },

                // Cookie Helpers
                setCookie(name, value, days) {
                    let expires = "";
                    if (days) {
                        const date = new Date();
                        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                        expires = "; expires=" + date.toUTCString();
                    }
                    document.cookie = name + "=" + (value || "") + expires + "; path=/";
                },

                getCookie(name) {
                    const nameEQ = name + "=";
                    const ca = document.cookie.split(';');
                    for (let i = 0; i < ca.length; i++) {
                        let c = ca[i];
                        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                    }
                    return null;
                }
            };
        }
    </script>
</x-marketplace.layout>
