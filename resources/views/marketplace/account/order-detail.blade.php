<x-marketplace.layout>
    @section('title', 'Order Details')

    <div class="bg-gray-100 py-6">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <!-- Back Button -->
                <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Back to Orders
                </a>

                <!-- Order Header -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-1">Order #{{ $order->order_number }}</h1>
                            <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('d M, Y h:i A') }}</p>
                        </div>
                        <span class="px-4 py-2 {{ $order->status_badge_class }} text-sm font-bold rounded-full uppercase mt-2 md:mt-0">
                            {{ str_replace('_', ' ', $order->order_status) }}
                        </span>
                    </div>

                    <!-- Order Progress -->
                    <div class="mt-6">
                        <div class="flex items-center justify-between relative">
                            <!-- Progress Line -->
                            <div class="absolute top-4 left-0 right-0 h-1 bg-gray-200">
                                <div class="h-full bg-green-500 transition-all" style="width: {{ $order->order_status === 'delivered' ? '100' : ($order->order_status === 'shipped' ? '66' : ($order->order_status === 'confirmed' ? '33' : '0')) }}%"></div>
                            </div>

                            <!-- Steps -->
                            <div class="flex justify-between w-full relative z-10">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full {{ in_array($order->order_status, ['confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered']) ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </div>
                                    <p class="text-xs text-gray-600 text-center">Confirmed</p>
                                </div>
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full {{ in_array($order->order_status, ['shipped', 'out_for_delivery', 'delivered']) ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </div>
                                    <p class="text-xs text-gray-600 text-center">Shipped</p>
                                </div>
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full {{ $order->order_status === 'delivered' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </div>
                                    <p class="text-xs text-gray-600 text-center">Delivered</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items (Grouped by Seller / Sub-Orders) -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Order Items</h2>
                    
                    @if($order->subOrders->isNotEmpty())
                        {{-- Show items grouped by sub-order (seller) --}}
                        @foreach($order->subOrders as $subOrder)
                            <div class="mb-6 last:mb-0 border border-gray-100 rounded-lg overflow-hidden">
                                {{-- Seller Header --}}
                                <div class="bg-gray-50 px-4 py-3 border-b flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-xelnova-green-100 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-xelnova-green-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $subOrder->seller->name ?? 'Seller' }}</p>
                                            <p class="text-xs text-gray-500">Order: {{ $subOrder->sub_order_number }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-2 py-1 {{ $subOrder->status_badge_class }} text-xs font-bold rounded uppercase">
                                            {{ str_replace('_', ' ', $subOrder->status) }}
                                        </span>
                                        @if($subOrder->tracking_number)
                                            <p class="text-xs text-gray-500 mt-1">
                                                Tracking: <span class="font-medium">{{ $subOrder->tracking_number }}</span>
                                                @if($subOrder->courier)
                                                    ({{ $subOrder->courier }})
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Items for this seller --}}
                                <div class="p-4 space-y-4">
                                    @foreach($subOrder->items as $item)
                                        <div class="flex gap-4">
                                            <a href="{{ $item->product?->slug ? route('marketplace.product.detail', $item->product->slug) : '#' }}" 
                                               class="w-16 h-16 flex-shrink-0 bg-gray-100 rounded overflow-hidden hover:opacity-75 transition">
                                                @if($item->product_image)
                                                    @php
                                                        $imageUrl = filter_var($item->product_image, FILTER_VALIDATE_URL) 
                                                            ? $item->product_image 
                                                            : asset('storage/' . $item->product_image);
                                                    @endphp
                                                    <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="w-full h-full object-contain">
                                                @endif
                                            </a>
                                            <div class="flex-1">
                                                <a href="{{ $item->product?->slug ? route('marketplace.product.detail', $item->product->slug) : '#' }}" 
                                                   class="font-medium text-gray-900 hover:text-blue-600 transition inline-block text-sm">
                                                    {{ $item->product_name }}
                                                </a>
                                                @if($item->variant_details && isset($item->variant_details['label']))
                                                    <p class="text-xs text-gray-500">{{ $item->variant_details['label'] }}</p>
                                                @elseif($item->variant)
                                                    <p class="text-xs text-gray-500">{{ $item->variant->variant_name }}</p>
                                                @endif
                                                <p class="text-xs text-gray-600">Qty: {{ $item->quantity }}</p>
                                                <span class="inline-block mt-1 text-[10px] uppercase font-bold {{ $item->is_inclusive_tax ? 'text-gray-500' : 'text-orange-600' }}">
                                                    {{ $item->is_inclusive_tax ? '(Tax Inclusive)' : '+ ' . number_format($item->tax_rate, 0) . '% Tax' }}
                                                </span>
                                                <br>
                                                <span class="inline-block mt-1 text-[10px] uppercase font-bold {{ $item->is_free_shipping ? 'text-green-600' : 'text-gray-500' }}">
                                                    {{ $item->is_free_shipping ? 'Free Shipping' : '+ ₹' . number_format($item->shipping_cost, 2) . ' Shipping' }}
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-gray-900 text-sm">₹{{ number_format($item->total, 2) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                {{-- Sub-order Total --}}
                                <div class="bg-gray-50 px-4 py-2 border-t flex justify-between text-sm">
                                    <span class="text-gray-600">Seller Total</span>
                                    <span class="font-bold text-gray-900">₹{{ number_format($subOrder->total, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        {{-- Fallback: Show items without sub-orders (for old orders) --}}
                        <div class="space-y-4">
                            @foreach($order->items as $item)
                                <div class="flex gap-4 pb-4 border-b last:border-0">
                                    <a href="{{ $item->product?->slug ? route('marketplace.product.detail', $item->product->slug) : '#' }}" 
                                       class="w-20 h-20 flex-shrink-0 bg-gray-100 rounded overflow-hidden hover:opacity-75 transition">
                                        @if($item->product_image)
                                            @php
                                                $imageUrl = filter_var($item->product_image, FILTER_VALIDATE_URL) 
                                                    ? $item->product_image 
                                                    : asset('storage/' . $item->product_image);
                                            @endphp
                                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="w-full h-full object-contain">
                                        @endif
                                    </a>
                                    <div class="flex-1">
                                        <a href="{{ $item->product?->slug ? route('marketplace.product.detail', $item->product->slug) : '#' }}" 
                                           class="font-medium text-gray-900 mb-1 hover:text-blue-600 transition inline-block">
                                            {{ $item->product_name }}
                                        </a>
                                        @if($item->variant_details && isset($item->variant_details['label']))
                                            <p class="text-xs text-gray-500">{{ $item->variant_details['label'] }}</p>
                                        @elseif($item->variant)
                                            <p class="text-xs text-gray-500">{{ $item->variant->variant_name }}</p>
                                        @endif
                                        <p class="text-sm text-gray-600">Qty: {{ $item->quantity }}</p>
                                        <p class="text-sm text-gray-600">Seller: {{ $item->seller->name ?? 'N/A' }}</p>
                                        <span class="inline-block mt-1 text-[10px] uppercase font-bold {{ $item->is_inclusive_tax ? 'text-gray-500' : 'text-orange-600' }}">
                                            {{ $item->is_inclusive_tax ? '(Tax Inclusive)' : '+ ' . number_format($item->tax_rate, 0) . '% Tax' }}
                                        </span>
                                        <br>
                                        <span class="inline-block mt-1 text-[10px] uppercase font-bold {{ $item->is_free_shipping ? 'text-green-600' : 'text-gray-500' }}">
                                            {{ $item->is_free_shipping ? 'Free Shipping' : '+ ₹' . number_format($item->shipping_cost, 2) . ' Shipping' }}
                                        </span>
                                        <span class="inline-block mt-2 px-2 py-1 {{ $item->status_badge_class }} text-xs font-bold rounded">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">₹{{ number_format($item->total, 2) }}</p>
                                        <p class="text-sm text-gray-500">₹{{ number_format($item->price, 2) }} each</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Delivery Address -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Delivery Address</h2>
                        <div class="text-sm text-gray-600">
                            <p class="font-medium text-gray-800">{{ $order->shipping_address['name'] }}</p>
                            <p>{{ $order->shipping_address['address_line_1'] }}</p>
                            @if($order->shipping_address['address_line_2'])
                                <p>{{ $order->shipping_address['address_line_2'] }}</p>
                            @endif
                            <p>{{ $order->shipping_address['city'] }}, {{ $order->shipping_address['state'] }} - {{ $order->shipping_address['pincode'] }}</p>
                            <p class="mt-2">Phone: {{ $order->shipping_address['phone'] }}</p>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Payment Summary</h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal (Inclusive of GST)</span>
                                <span class="text-gray-900">₹{{ number_format($order->total - $order->shipping_charge + $order->discount, 2) }}</span>
                            </div>
                            @if($order->discount > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>Discount</span>
                                    <span>- ₹{{ number_format($order->discount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="text-gray-900">{{ $order->shipping_charge > 0 ? '₹' . number_format($order->shipping_charge, 2) : 'Free' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (GST)</span>
                                <span class="text-gray-900">₹{{ number_format($order->tax, 2) }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t font-bold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-gray-900">₹{{ number_format($order->total, 2) }}</span>
                            </div>
                            <div class="pt-2 border-t">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-600">Payment Method</span>
                                    <span class="text-gray-900">{{ ucfirst($order->payment_method) }}</span>
                                </div>
                                <div class="flex justify-between text-xs mt-1">
                                    <span class="text-gray-600">Payment Status</span>
                                    <span class="px-2 py-0.5 {{ $order->payment_badge_class }} text-xs font-bold rounded">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
