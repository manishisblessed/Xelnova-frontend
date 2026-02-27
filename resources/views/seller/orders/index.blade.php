<x-seller.layout>
    @section('title', 'Order Management')

    <div class="bg-white rounded-lg shadow-sm">
        <!-- Header -->
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
            <p class="text-sm text-gray-600 mt-1">Manage your customer orders and shipments</p>
        </div>

        <!-- Filters -->
        <div class="p-4 border-b flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
                @php
                    $currentStatus = request('status', 'all');
                    $statuses = [
                        'all' => 'All Orders',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled'
                    ];
                @endphp

                @foreach($statuses as $value => $label)
                    <a href="{{ route('seller.orders', ['status' => $value, 'search' => request('search')]) }}" 
                       class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition {{ $currentStatus == $value ? 'bg-xelnova-green-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            
            <form action="{{ route('seller.orders') }}" method="GET" class="relative w-full md:w-64">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID, Name..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-xelnova-green-500 focus:border-xelnova-green-500 text-sm">
                <button type="submit" class="absolute left-3 top-3 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Orders Table -->
        @if($subOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3">Sub-Order ID</th>
                            <th class="px-6 py-3">Items</th>
                            <th class="px-6 py-3">Customer</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Amount</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subOrders as $subOrder)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $subOrder->sub_order_number }}</div>
                                    <div class="text-xs text-gray-500">Parent: {{ $subOrder->order->order_number }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2">
                                        @foreach($subOrder->items->take(2) as $item)
                                            <div class="flex items-center gap-3">
                                                @if($item->product_image)
                                                    @php
                                                        $imageUrl = filter_var($item->product_image, FILTER_VALIDATE_URL) 
                                                            ? $item->product_image 
                                                            : asset('storage/' . $item->product_image);
                                                    @endphp
                                                    <img src="{{ $imageUrl }}" class="w-10 h-10 rounded object-cover">
                                                @else
                                                    <div class="w-10 h-10 bg-gray-200 rounded"></div>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-medium text-gray-900 truncate">{{ Str::limit($item->product_name, 30) }}</div>
                                                    @if($item->variant_details && isset($item->variant_details['label']))
                                                        <div class="text-xs text-gray-500">{{ Str::limit($item->variant_details['label'], 30) }}</div>
                                                    @endif
                                                    <div class="text-xs text-gray-500">Qty: {{ $item->quantity }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($subOrder->items->count() > 2)
                                            <div class="text-xs text-gray-500">+{{ $subOrder->items->count() - 2 }} more item(s)</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <div class="font-medium">{{ $subOrder->order->user->name }}</div>
                                    @if($subOrder->order->shipping_address)
                                        <div class="text-xs text-gray-500">
                                            {{ $subOrder->order->shipping_address['city'] ?? '-' }}, {{ $subOrder->order->shipping_address['state'] ?? '-' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ $subOrder->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">₹{{ number_format($subOrder->total, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="{{ $subOrder->status_badge_class }} text-xs font-medium px-2.5 py-0.5 rounded uppercase">
                                        {{ str_replace('_', ' ', $subOrder->status) }}
                                    </span>
                                    @if($subOrder->tracking_number)
                                        <div class="text-xs text-gray-500 mt-1">Track: {{ $subOrder->tracking_number }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('seller.orders.detail', $subOrder->id) }}" class="text-blue-600 hover:text-blue-800 font-medium text-xs border border-blue-600 px-3 py-1 rounded hover:bg-blue-50 transition">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-t">
                {{ $subOrders->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-gray-400 mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Orders Yet</h3>
                <p class="text-gray-600">You haven't received any orders yet. Your orders will appear here once customers purchase your products.</p>
            </div>
        @endif
    </div>
</x-seller.layout>
