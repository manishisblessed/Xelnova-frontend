<x-marketplace.layout>
    @section('title', 'My Orders')

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
                            <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-3 py-2 bg-blue-50 text-blue-600 rounded font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                                My Orders
                            </a>
                            <a href="{{ route('account.wishlist') }}" class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded">
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

                <!-- Orders List -->
                <div class="w-full lg:w-3/4">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 border-b">
                            <h1 class="text-xl font-bold text-gray-900">My Orders</h1>
                        </div>

                        @if($orders->isEmpty())
                            <!-- Empty State -->
                            <div class="p-12 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-24 h-24 mx-auto text-gray-300 mb-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                                <h2 class="text-2xl font-medium text-gray-600 mb-2">No orders yet</h2>
                                <p class="text-gray-500 mb-6">You haven't placed any orders yet.</p>
                                <a href="{{ route('marketplace.products') }}" class="inline-block bg-xelnova-green-600 hover:bg-xelnova-green-700 text-white font-bold py-3 px-8 rounded shadow-sm transition">
                                    Start Shopping
                                </a>
                            </div>
                        @else
                            <!-- Orders -->
                            <div class="divide-y">
                                @foreach($orders as $order)
                                    <div class="p-4 hover:bg-gray-50 transition">
                                        <!-- Order Header -->
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                                            <div class="flex items-center gap-4 mb-2 md:mb-0">
                                                <div>
                                                    <p class="text-sm text-gray-500">Order #{{ $order->order_number }}</p>
                                                    <p class="text-xs text-gray-400">Placed on {{ $order->created_at->format('d M, Y') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="px-3 py-1 {{ $order->status_badge_class }} text-xs font-bold rounded-full uppercase">
                                                    {{ str_replace('_', ' ', $order->order_status) }}
                                                </span>
                                                <a href="{{ route('account.order.detail', $order->order_number) }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                                    View Details →
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Order Items -->
                                        <div class="space-y-3">
                                            @foreach($order->items->take(2) as $item)
                                                <div class="flex gap-4">
                                                    <div class="w-16 h-16 flex-shrink-0 bg-gray-100 rounded overflow-hidden">
                                                        @if($item->product_image)
                                                            @php
                                                                $imageUrl = file_url($item->product_image);
                                                            @endphp
                                                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="w-full h-full object-contain">
                                                        @endif
                                                    </div>
                                                    <div class="flex-1">
                                                        <h4 class="font-medium text-gray-900 text-sm">{{ $item->product_name }}</h4>
                                                        @if($item->variant_details && isset($item->variant_details['label']))
                                                            <p class="text-xs text-gray-500">{{ $item->variant_details['label'] }}</p>
                                                        @elseif($item->variant)
                                                            <p class="text-xs text-gray-500">{{ $item->variant->variant_name }}</p>
                                                        @endif
                                                        <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="font-bold text-gray-900">₹{{ number_format($item->total, 2) }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            @if($order->items->count() > 2)
                                                <p class="text-sm text-gray-500">+ {{ $order->items->count() - 2 }} more item(s)</p>
                                            @endif
                                        </div>

                                        <!-- Order Total -->
                                        <div class="mt-4 pt-4 border-t flex justify-between items-center">
                                            <p class="text-sm text-gray-600">Total Amount</p>
                                            <p class="text-lg font-bold text-gray-900">₹{{ number_format($order->total, 2) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if($orders->hasPages())
                                <div class="p-4 border-t">
                                    {{ $orders->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
