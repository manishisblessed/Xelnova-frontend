<x-seller.layout>
    @section('title', $product ? 'Edit Product' : 'Add New Product')

    @php
        // Define which fields belong to which tab
        $tabFields = [
            'basic' => ['name', 'category_id', 'brand_id', 'sku', 'barcode', 'short_description', 'is_active', 'is_featured'],
            'pricing' => ['price', 'compare_at_price', 'quantity', 'stock_status', 'hsn_code', 'gst_rate', 'is_inclusive_tax'],
            'description' => ['description'],
            'images' => ['main_image', 'gallery_images'],
            'seo' => ['meta_title', 'meta_description', 'meta_keywords'],
            'shipping' => ['requires_shipping', 'weight', 'length', 'width', 'height', 'is_fragile'],
            'variants' => ['variant_options_data', 'variants_data'],
        ];
        
        // Check which tabs have errors
        $tabErrors = [];
        foreach ($tabFields as $tabKey => $fields) {
            foreach ($fields as $field) {
                if ($errors->has($field)) {
                    $tabErrors[$tabKey] = true;
                    break;
                }
            }
        }
    @endphp

    @php
        $mainImageUrl = $product ? $product->main_image_url : '';
        $galleryImagesData = $product ? $product->images->map(fn($img) => ['id' => $img->id, 'url' => $img->image_url]) : [];
    @endphp

    <div class="bg-white rounded-lg shadow-sm" x-data="productForm()">
        <!-- Tabs -->
        <div class="border-b px-6 flex gap-4 overflow-x-auto">
            <button type="button" @click="tab = 'basic'" :class="{'border-xelnova-green-600 text-xelnova-green-600': tab === 'basic', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'basic'}" class="py-4 border-b-2 font-medium text-sm transition whitespace-nowrap flex items-center gap-2 {{ isset($tabErrors['basic']) ? 'text-red-600' : '' }}">
                Basic Info
                @if(isset($tabErrors['basic']))
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </button>
            <button type="button" @click="tab = 'pricing'" :class="{'border-xelnova-green-600 text-xelnova-green-600': tab === 'pricing', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'pricing'}" class="py-4 border-b-2 font-medium text-sm transition whitespace-nowrap flex items-center gap-2 {{ isset($tabErrors['pricing']) ? 'text-red-600' : '' }}">
                Pricing & Stock
                @if(isset($tabErrors['pricing']))
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </button>
            <button type="button" @click="tab = 'description'" :class="{'border-xelnova-green-600 text-xelnova-green-600': tab === 'description', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'description'}" class="py-4 border-b-2 font-medium text-sm transition whitespace-nowrap flex items-center gap-2 {{ isset($tabErrors['description']) ? 'text-red-600' : '' }}">
                Description
                @if(isset($tabErrors['description']))
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </button>
            <button type="button" @click="tab = 'images'" :class="{'border-xelnova-green-600 text-xelnova-green-600': tab === 'images', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'images'}" class="py-4 border-b-2 font-medium text-sm transition whitespace-nowrap flex items-center gap-2 {{ isset($tabErrors['images']) ? 'text-red-600' : '' }}">
                Images
                @if(isset($tabErrors['images']))
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </button>
            <button type="button" @click="tab = 'seo'" :class="{'border-xelnova-green-600 text-xelnova-green-600': tab === 'seo', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'seo'}" class="py-4 border-b-2 font-medium text-sm transition whitespace-nowrap flex items-center gap-2 {{ isset($tabErrors['seo']) ? 'text-red-600' : '' }}">
                SEO
                @if(isset($tabErrors['seo']))
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </button>
            <button type="button" @click="tab = 'shipping'" :class="{'border-xelnova-green-600 text-xelnova-green-600': tab === 'shipping', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'shipping'}" class="py-4 border-b-2 font-medium text-sm transition whitespace-nowrap flex items-center gap-2 {{ isset($tabErrors['shipping']) ? 'text-red-600' : '' }}">
                Shipping
                @if(isset($tabErrors['shipping']))
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </button>
            <button type="button" @click="tab = 'variants'" :class="{'border-xelnova-green-600 text-xelnova-green-600': tab === 'variants', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'variants'}" class="py-4 border-b-2 font-medium text-sm transition whitespace-nowrap flex items-center gap-2 {{ isset($tabErrors['variants']) ? 'text-red-600' : '' }}">
                Variants
                @if(isset($tabErrors['variants']))
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </button>
        </div>

        <div class="p-6">
            <form action="{{ $product ? route('seller.products.update', $product) : route('seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if($product)
                    @method('PUT')
                @endif
                
                <!-- Hidden field for deleted images -->
                <input type="hidden" name="deleted_images" :value="deletedImages.join(',')">
                
                <!-- Hidden fields for variants -->
                <input type="hidden" name="has_variants" :value="hasVariants ? '1' : '0'">
                <template x-if="hasVariants">
                    <div>
                        <input type="hidden" name="variant_options_data" :value="getVariantOptionsJson()">
                        <input type="hidden" name="variants_data" :value="getVariantsJson()">
                    </div>
                </template>
                
                <!-- Basic Info Tab -->
                <div x-show="tab === 'basic'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Product Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="e.g. Samsung Galaxy S24 Ultra">
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                            <select name="category_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->full_path }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Brand</label>
                            <select name="brand_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500">
                                <option value="">-- No Brand --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="e.g. SAM-S24U-256-GRY">
                            @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Barcode</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="Enter barcode (ISBN, UPC, etc.)">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Short Description</label>
                        <textarea name="short_description" rows="2" maxlength="500" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="Brief product summary (max 500 characters)">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-xelnova-green-600 focus:ring-xelnova-green-500">
                            <label for="is_active" class="text-sm text-gray-700">Active</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-xelnova-green-600 focus:ring-xelnova-green-500">
                            <label for="is_featured" class="text-sm text-gray-700">Featured Product</label>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Stock Tab -->
                <div x-show="tab === 'pricing'" class="space-y-6" x-cloak>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Selling Price (₹) <span class="text-red-500">*</span></label>
                            <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $product->price ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="0.00">
                            @error('price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Compare At Price (MRP ₹)</label>
                            <input type="number" name="compare_at_price" step="0.01" min="0" value="{{ old('compare_at_price', $product->compare_at_price ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="0.00">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Stock Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" min="0" value="{{ old('quantity', $product->quantity ?? 0) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="0">
                            @error('quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Stock Status <span class="text-red-500">*</span></label>
                            <select name="stock_status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500">
                                <option value="in_stock" {{ old('stock_status', $product->stock_status ?? 'in_stock') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                <option value="out_of_stock" {{ old('stock_status', $product->stock_status ?? '') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                <option value="backorder" {{ old('stock_status', $product->stock_status ?? '') == 'backorder' ? 'selected' : '' }}>Available for Backorder</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">HSN Code</label>
                            <input type="text" name="hsn_code" maxlength="20" value="{{ old('hsn_code', $product->hsn_code ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="Enter HSN Code">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">GST Rate (%) <span class="text-red-500">*</span></label>
                            <select name="gst_rate" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500">
                                @foreach($gstRates as $rate)
                                    <option value="{{ $rate['id'] }}" {{ old('gst_rate', $product->gst_rate ?? '18') == $rate['id'] ? 'selected' : '' }}>
                                        {{ $rate['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_inclusive_tax" id="is_inclusive_tax" value="1" {{ old('is_inclusive_tax', $product->is_inclusive_tax ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-xelnova-green-600 focus:ring-xelnova-green-500">
                        <label for="is_inclusive_tax" class="text-sm text-gray-700">Selling price includes GST</label>
                    </div>
                </div>

                <!-- Description Tab -->
                <div x-show="tab === 'description'" class="space-y-6" x-cloak>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Full Description</label>
                        <textarea name="description" rows="8" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="Enter detailed product description, features, specifications...">{{ old('description', $product->description ?? '') }}</textarea>
                        <p class="text-xs text-gray-500">Provide comprehensive product details, features, and specifications</p>
                    </div>

                    <!-- Bulleted Highlights -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Bulleted Highlights</label>
                            <button type="button" @click="addHighlight()" class="text-sm text-xelnova-green-600 hover:text-xelnova-green-700 font-medium">
                                + Add Highlight
                            </button>
                        </div>
                        
                        <div class="space-y-2">
                            <template x-if="highlights.length === 0">
                                <p class="text-sm text-gray-500 italic bg-gray-50 p-3 rounded-md border border-dashed text-center">
                                    No highlights added. Add highlights to show key features in a bulleted list.
                                </p>
                            </template>

                            <template x-for="(highlight, index) in highlights" :key="index">
                                <div class="flex items-center gap-2">
                                    <div class="flex-none bg-gray-100 p-2 rounded text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="text" :name="'highlights[' + index + ']'" x-model="highlights[index]" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500 text-sm" placeholder="e.g. Fast Charging Support">
                                    <button type="button" @click="removeHighlight(index)" class="text-gray-400 hover:text-red-500 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Images Tab -->
                <div x-show="tab === 'images'" class="space-y-6" x-cloak>
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Main Product Image</label>
                        <div class="flex items-start gap-4">
                            <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg overflow-hidden">
                                <template x-if="mainImagePreview">
                                    <img :src="mainImagePreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!mainImagePreview">
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <input type="file" name="main_image" accept="image/*" @change="handleMainImage($event)" class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-xelnova-green-50 file:text-xelnova-green-700 hover:file:bg-xelnova-green-100">
                                <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF or WebP (max 2MB)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Gallery Images</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition cursor-pointer" @click="$refs.galleryInput.click()">
                            <input type="file" name="gallery_images[]" multiple accept="image/*" x-ref="galleryInput" @change="addGalleryImages($event)" class="hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-gray-400 mb-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <p class="text-sm text-gray-600 font-medium">Click to add gallery images</p>
                            <p class="text-xs text-gray-500 mt-1">Multiple files allowed</p>
                        </div>
                        
                        <template x-if="galleryImages.length > 0">
                            <div class="grid grid-cols-3 md:grid-cols-5 gap-4 mt-4">
                                <template x-for="(img, index) in galleryImages" :key="index">
                                    <div class="relative group">
                                        <img :src="img.url" class="w-full h-24 object-cover rounded-lg border">
                                        <button type="button" @click="removeGalleryImage(index)" class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- SEO Tab -->
                <div x-show="tab === 'seo'" class="space-y-6" x-cloak>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                        <input type="text" name="meta_title" maxlength="255" value="{{ old('meta_title', $product->meta_title ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="SEO title for search engines">
                        <p class="text-xs text-gray-500">Recommended: 50-60 characters</p>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                        <textarea name="meta_description" rows="3" maxlength="500" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="Brief description for search engine results">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
                        <p class="text-xs text-gray-500">Recommended: 150-160 characters</p>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $product->meta_keywords ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="keyword1, keyword2, keyword3">
                        <p class="text-xs text-gray-500">Comma-separated keywords</p>
                    </div>
                </div>

                <!-- Shipping Tab -->
                <div x-show="tab === 'shipping'" class="space-y-6" x-cloak>
                    <div class="flex items-center gap-3 mb-4">
                        <input type="checkbox" name="requires_shipping" id="requires_shipping" value="1" {{ old('requires_shipping', $product->requires_shipping ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-xelnova-green-600 focus:ring-xelnova-green-500">
                        <label for="requires_shipping" class="text-sm text-gray-700">This is a physical product that requires shipping</label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-gray-50 rounded-lg" x-show="document.querySelector('[name=requires_shipping]').checked">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Shipping Cost (₹)</label>
                            <input type="number" name="shipping_cost" step="0.01" min="0" value="{{ old('shipping_cost', $product->shipping_cost ?? 0) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="0.00">
                            <p class="text-xs text-gray-500">Flat shipping rate for this item</p>
                        </div>
                        <div class="flex items-center gap-3 pt-6">
                            <input type="checkbox" name="is_free_shipping" id="is_free_shipping" value="1" {{ old('is_free_shipping', $product->is_free_shipping ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-xelnova-green-600 focus:ring-xelnova-green-500">
                            <label for="is_free_shipping" class="text-sm text-gray-700 font-medium">Free Shipping (Cost included in price)</label>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                            <input type="number" name="weight" step="0.01" min="0" value="{{ old('weight', $product->weight ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="0.0">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Length (cm)</label>
                            <input type="number" name="length" step="0.01" min="0" value="{{ old('length', $product->length ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="0.0">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Width (cm)</label>
                            <input type="number" name="width" step="0.01" min="0" value="{{ old('width', $product->width ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="0.0">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Height (cm)</label>
                            <input type="number" name="height" step="0.01" min="0" value="{{ old('height', $product->height ?? '') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="0.0">
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_fragile" id="is_fragile" value="1" {{ old('is_fragile', $product->is_fragile ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-xelnova-green-600 focus:ring-xelnova-green-500">
                        <label for="is_fragile" class="text-sm text-gray-700">Fragile item - requires careful handling</label>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Packaging Type</label>
                        <select name="packaging_type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500">
                            <option value="box" {{ old('packaging_type', $product->packaging_type ?? 'box') == 'box' ? 'selected' : '' }}>Box</option>
                            <option value="flyer" {{ old('packaging_type', $product->packaging_type ?? '') == 'flyer' ? 'selected' : '' }}>Flyer</option>
                        </select>
                        <p class="text-xs text-gray-500">Select the type of packaging used</p>
                    </div>
                </div>

                <!-- Variants Tab -->
                <div x-show="tab === 'variants'" class="space-y-6" x-cloak>
                    <div class="flex items-center gap-3 p-4 bg-blue-50 rounded-lg">
                        <input type="checkbox" x-model="hasVariants" id="has_variants_toggle" class="rounded border-gray-300 text-xelnova-green-600 focus:ring-xelnova-green-500">
                        <label for="has_variants_toggle" class="text-sm font-medium text-gray-900">This product has multiple variants (e.g., different colors, sizes)</label>
                    </div>

                    <template x-if="!hasVariants">
                        <div class="text-center py-8 text-gray-500">
                            <p>Enable variants to create multiple versions of this product with different attributes.</p>
                        </div>
                    </template>

                    <template x-if="hasVariants">
                        <div class="space-y-6">
                            <template x-if="categoryVariantTypes.length === 0">
                                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800">
                                    <p class="font-medium">No variant types available for this category</p>
                                    <p class="text-sm mt-1">Please select a category that has variant types assigned, or contact admin to set up variant types for this category.</p>
                                </div>
                            </template>

                            <template x-if="categoryVariantTypes.length > 0">
                                <div class="space-y-6">
                                    <!-- Variant Options Section -->
                                    <div class="border rounded-lg p-4">
                                        <h3 class="text-lg font-medium text-gray-900 mb-4">Define Variant Options</h3>
                                        
                                        <template x-for="variantType in categoryVariantTypes" :key="variantType.id">
                                            <div class="mb-6 last:mb-0">
                                                <div class="flex items-center justify-between mb-3">
                                                    <label class="block text-sm font-medium text-gray-700" x-text="variantType.name"></label>
                                                    <button type="button" @click="addVariantOption(variantType.id)" class="text-sm text-xelnova-green-600 hover:text-xelnova-green-700 font-medium">
                                                        + Add <span x-text="variantType.name"></span>
                                                    </button>
                                                </div>
                                                
                                                <div class="space-y-2">
                                                    <template x-if="!variantOptions[variantType.id] || variantOptions[variantType.id].length === 0">
                                                        <p class="text-sm text-gray-500 italic">No options added yet</p>
                                                    </template>
                                                    
                                                    <template x-if="variantOptions[variantType.id] && variantOptions[variantType.id].length > 0">
                                                        <div class="space-y-2">
                                                            <template x-for="(option, index) in variantOptions[variantType.id]" :key="option.temp_id || option.id || index">
                                                                <div class="flex items-center gap-3">
                                                                    <input type="text" x-model="option.value" @blur="option.display_value = option.display_value || option.value" class="w-32 border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500 text-sm" placeholder="Value (e.g., blue)">
                                                                    <input type="text" x-model="option.display_value" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500 text-sm" placeholder="Display Name (e.g., Midnight Blue)">
                                                                    <template x-if="variantType.input_type === 'color'">
                                                                        <input type="color" x-model="option.color_code" class="w-12 h-10 border-gray-300 rounded-md shadow-sm">
                                                                    </template>
                                                                    <button type="button" @click="removeVariantOption(variantType.id, index)" class="text-red-500 hover:text-red-700">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Generate Combinations Button -->
                                    <div class="flex justify-center">
                                        <button type="button" @click="generateVariantCombinations()" class="px-6 py-3 bg-xelnova-green-600 text-white rounded-md hover:bg-xelnova-green-700 font-medium">
                                            Generate Variant Combinations
                                        </button>
                                    </div>

                                    <!-- Variants Matrix -->
                                    <template x-if="variants.length > 0">
                                        <div class="border rounded-lg p-4">
                                            <h3 class="text-lg font-medium text-gray-900 mb-4">Variant Combinations</h3>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-300">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Combination</th>
                                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selling Price (₹)</th>
                                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MRP (₹)</th>
                                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Show this variant separately in product listings">List Separately</th>
                                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <template x-for="(variant, index) in variants" :key="index">
                                                            <tr>
                                                                <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900" x-text="getVariantLabel(variant)"></td>
                                                                <td class="px-3 py-3 whitespace-nowrap">
                                                                    <input type="text" x-model="variant.sku" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500 text-sm">
                                                                </td>
                                                                <td class="px-3 py-3 whitespace-nowrap">
                                                                    <input type="number" x-model="variant.price" step="0.01" min="0" class="w-24 border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500 text-sm" placeholder="0.00">
                                                                </td>
                                                                <td class="px-3 py-3 whitespace-nowrap">
                                                                    <input type="number" x-model="variant.compare_at_price" step="0.01" min="0" class="w-24 border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500 text-sm" placeholder="0.00">
                                                                </td>
                                                                <td class="px-3 py-3 whitespace-nowrap">
                                                                    <input type="number" x-model="variant.quantity" min="0" class="w-20 border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500 text-sm">
                                                                </td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-center">
                                                                    <input type="checkbox" x-model="variant.show_in_listing" class="rounded border-gray-300 text-xelnova-green-600 focus:ring-xelnova-green-500" title="Show this variant separately in product listings">
                                                                </td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-center">
                                                                    <input type="radio" :name="'default_variant'" :checked="variant.is_default" @change="setDefaultVariant(index)" class="rounded-full border-gray-300 text-xelnova-green-600 focus:ring-xelnova-green-500">
                                                                </td>
                                                                <td class="px-3 py-3 whitespace-nowrap text-center">
                                                                    <input type="checkbox" x-model="variant.is_active" class="rounded border-gray-300 text-xelnova-green-600 focus:ring-xelnova-green-500">
                                                                </td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="pt-6 border-t flex justify-end gap-4">
                    <a href="{{ route('seller.products') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-xelnova-green-600 text-white rounded-md hover:bg-xelnova-green-700 font-medium">
                        {{ $product ? 'Update Product' : 'Save Product' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function productForm() {
            return {
                tab: '{{ old('_tab', 'basic') }}',
                mainImagePreview: '{!! $mainImageUrl !!}',
                galleryImages: @json($galleryImagesData),
                deletedImages: [],
                hasVariants: {{ old('has_variants', $product->has_variants ?? false) ? 'true' : 'false' }},
                categoryVariantTypes: @json($categoryVariantTypes ?? []),
                highlights: @json(old('highlights', $product->highlights ?? [])),
                variantOptions: {},
                variants: [],
                tempIdCounter: 1,
                
                init() {
                    const existingOptions = @json($existingVariantOptions ?? []);
                    if (Object.keys(existingOptions).length > 0) {
                        this.variantOptions = existingOptions;
                    }
                    const existingVariants = @json($existingVariants ?? []);
                    if (existingVariants.length > 0) {
                        this.variants = existingVariants;
                    }

                    // Ensure highlights is an array
                    if (!Array.isArray(this.highlights)) {
                        this.highlights = [];
                    }
                },
                
                addHighlight() {
                    this.highlights.push('');
                },

                removeHighlight(index) {
                    this.highlights.splice(index, 1);
                },
                
                handleMainImage(e) {
                    if (e.target.files.length > 0) {
                        this.mainImagePreview = URL.createObjectURL(e.target.files[0]);
                    }
                },
                
                addGalleryImages(e) {
                    for (let file of e.target.files) {
                        this.galleryImages.push({
                            id: null,
                            url: URL.createObjectURL(file),
                            file: file
                        });
                    }
                },
                
                removeGalleryImage(index) {
                    const img = this.galleryImages[index];
                    if (img.id) {
                        this.deletedImages.push(img.id);
                    }
                    this.galleryImages.splice(index, 1);
                },
                
                addVariantOption(variantTypeId) {
                    if (!this.variantOptions[variantTypeId]) {
                        this.variantOptions[variantTypeId] = [];
                    }
                    this.variantOptions[variantTypeId].push({
                        temp_id: 'temp_' + this.tempIdCounter++,
                        value: '',
                        display_value: '',
                        color_code: ''
                    });
                },
                
                removeVariantOption(variantTypeId, index) {
                    this.variantOptions[variantTypeId].splice(index, 1);
                    if (this.variantOptions[variantTypeId].length === 0) {
                        delete this.variantOptions[variantTypeId];
                    }
                    if (this.variants.length > 0) {
                        this.generateVariantCombinations();
                    }
                },
                
                generateVariantCombinations() {
                    const optionArrays = [];
                    for (let typeId in this.variantOptions) {
                        if (this.variantOptions[typeId].length > 0) {
                            optionArrays.push(this.variantOptions[typeId]);
                        }
                    }
                    if (optionArrays.length === 0) {
                        this.variants = [];
                        return;
                    }
                    const combinations = this.cartesianProduct(optionArrays);
                    const basePrice = parseFloat(document.querySelector('[name=price]').value) || 0;
                    const baseCompareAtPrice = parseFloat(document.querySelector('[name=compare_at_price]').value) || null;
                    const baseSku = document.querySelector('[name=sku]').value || 'VARIANT';
                    this.variants = combinations.map((combo, index) => {
                        const optionIds = combo.map(opt => opt.temp_id || opt.id);
                        const skuParts = combo.map(opt => opt.value.toUpperCase().replace(/\s+/g, '-'));
                        return {
                            sku: baseSku + '-' + skuParts.join('-'),
                            price: basePrice,
                            compare_at_price: baseCompareAtPrice,
                            quantity: 0,
                            stock_status: 'in_stock',
                            option_ids: optionIds,
                            is_default: index === 0,
                            is_active: true,
                            show_in_listing: false
                        };
                    });
                },
                
                cartesianProduct(arrays) {
                    if (arrays.length === 0) return [[]];
                    if (arrays.length === 1) return arrays[0].map(item => [item]);
                    const result = [];
                    const firstArray = arrays[0];
                    const otherArrays = arrays.slice(1);
                    const otherProducts = this.cartesianProduct(otherArrays);
                    for (let item of firstArray) {
                        for (let product of otherProducts) {
                            result.push([item, ...product]);
                        }
                    }
                    return result;
                },
                
                getVariantLabel(variant) {
                    const labels = [];
                    for (let optionId of variant.option_ids) {
                        for (let typeId in this.variantOptions) {
                            const option = this.variantOptions[typeId].find(o => (o.temp_id || o.id) === optionId);
                            if (option) {
                                labels.push(option.display_value || option.value);
                                break;
                            }
                        }
                    }
                    return labels.join(' / ');
                },
                
                setDefaultVariant(index) {
                    this.variants.forEach((v, i) => v.is_default = (i === index));
                },
                
                getVariantOptionsJson() {
                    const result = [];
                    for (let typeId in this.variantOptions) {
                        for (let option of this.variantOptions[typeId]) {
                            result.push({
                                variant_type_id: parseInt(typeId),
                                temp_id: option.temp_id || option.id,
                                value: option.value,
                                display_value: option.display_value || option.value,
                                color_code: option.color_code || null
                            });
                        }
                    }
                    return JSON.stringify(result);
                },
                
                getVariantsJson() {
                    return JSON.stringify(this.variants);
                }
            }
        }
    </script>
    @endpush
</x-seller.layout>
