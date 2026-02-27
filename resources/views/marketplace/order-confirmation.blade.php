<x-marketplace.layout>
    @section('title', 'Order Confirmed')

    <div class="bg-gray-100 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <!-- Success Message -->
                <div class="bg-white rounded-lg shadow-sm p-8 text-center mb-6">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10 text-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Order Placed Successfully!</h1>
                    <p class="text-gray-600 mb-4">Thank you for your order. Your order has been confirmed.</p>
                    <div class="bg-gray-50 rounded-lg p-4 inline-block">
                        <p class="text-sm text-gray-600">Order Number</p>
                        <p class="text-xl font-bold text-gray-900">{{ $order->order_number }}</p>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 pb-3 border-b">Order Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 mb-2">Delivery Address</h3>
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
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 mb-2">Order Summary</h3>
                            <div class="text-sm space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Order Date:</span>
                                    <span class="font-medium">{{ $order->created_at->format('d M, Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Method:</span>
                                    <span class="font-medium">{{ ucfirst($order->payment_method) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Status:</span>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded">{{ ucfirst($order->payment_status) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="border-t pt-4">
                        <h3 class="text-sm font-bold text-gray-700 mb-3">Items Ordered</h3>
                        <div class="space-y-4">
                            @foreach($order->items as $item)
                                <div class="flex gap-4 pb-4 border-b last:border-0">
                                    <div class="w-20 h-20 flex-shrink-0 bg-gray-100 rounded overflow-hidden">
                                        @if($item->product_image)
                                            @php
                                                $imageUrl = filter_var($item->product_image, FILTER_VALIDATE_URL) 
                                                    ? $item->product_image 
                                                    : asset('storage/' . $item->product_image);
                                            @endphp
                                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="w-full h-full object-contain">
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 mb-1">{{ $item->product_name }}</h4>
                                        @if($item->variant_details && isset($item->variant_details['label']))
                                            <p class="text-xs text-gray-500 mb-1">{{ $item->variant_details['label'] }}</p>
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
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">₹{{ number_format($item->total, 2) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 pb-3 border-b">Payment Summary</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-800">
                            <span>Subtotal (Inclusive of GST)</span>
                            <span>₹{{ number_format($order->total - $order->shipping_charge + $order->discount, 2) }}</span>
                        </div>
                        @if($order->discount > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Discount</span>
                                <span>- ₹{{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-gray-800">
                            <span>Shipping Charges</span>
                            <span>{{ $order->shipping_charge > 0 ? '₹' . number_format($order->shipping_charge, 2) : 'Free' }}</span>
                        </div>
                        <div class="flex justify-between text-gray-800">
                            <span>Tax (GST)</span>
                            <span>₹{{ number_format($order->tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-gray-900 pt-3 border-t">
                            <span>Total Amount</span>
                            <span>₹{{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4">
                    <a href="{{ route('account.orders') }}" 
                       class="flex-1 bg-xelnova-green-600 hover:bg-xelnova-green-700 text-white font-bold py-3 px-6 rounded shadow-sm transition text-center uppercase text-sm">
                        View My Orders
                    </a>
                    <a href="{{ route('home') }}" 
                       class="flex-1 bg-white hover:bg-gray-50 text-gray-800 font-bold py-3 px-6 rounded shadow-sm border border-gray-300 transition text-center uppercase text-sm">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
