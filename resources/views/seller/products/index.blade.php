<x-seller.layout>
    @section('title', 'Products')

    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b flex flex-col md:flex-row justify-between items-center gap-4">
            <form method="GET" action="{{ route('seller.products') }}" class="flex items-center gap-4 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-xelnova-green-500 focus:border-xelnova-green-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 absolute left-3 top-3 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <select name="category_id" onchange="this.form.submit()" class="border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-xelnova-green-500 focus:border-xelnova-green-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->full_path }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg text-sm">Filter</button>
            </form>
            
            <a href="{{ route('seller.products.create') }}" class="bg-xelnova-green-600 hover:bg-xelnova-green-700 text-white font-medium py-2 px-4 rounded-lg text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add New Product
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">SKU</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">GST</th>
                        <th class="px-6 py-3">Stock</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($product->main_image)
                                        <img src="{{ $product->main_image_url }}" class="w-10 h-10 rounded object-cover" alt="{{ $product->name }}">
                                    @else
                                        <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $product->category->full_path ?? 'Uncategorized' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $product->sku ?: '-' }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">₹{{ number_format($product->price, 2) }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $product->gst_rate }}%</td>
                            <td class="px-6 py-4">
                                @if($product->quantity > 10)
                                    <span class="text-green-600 font-medium">{{ $product->quantity }}</span>
                                @elseif($product->quantity > 0)
                                    <span class="text-yellow-600 font-medium">{{ $product->quantity }}</span>
                                @else
                                    <span class="text-red-600 font-medium">0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($product->status === 'approved')
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Approved</span>
                                @elseif($product->status === 'pending')
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Pending</span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Rejected</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('seller.products.edit', $product) }}" class="text-blue-600 hover:text-blue-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('seller.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <p class="text-lg mb-2">No products found</p>
                                <p class="text-sm">Start by adding your first product.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
            <div class="p-4 border-t flex justify-between items-center">
                <span class="text-sm text-gray-500">
                    Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }} products
                </span>
                <div class="flex gap-2">
                    @if($products->onFirstPage())
                        <span class="px-3 py-1 border rounded text-sm opacity-50">Previous</span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Previous</a>
                    @endif
                    
                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Next</a>
                    @else
                        <span class="px-3 py-1 border rounded text-sm opacity-50">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-seller.layout>
