<x-seller.layout>
    @section('title', 'Dashboard')

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Total Sales -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
                {{-- <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">+12.5%</span> --}}
            </div>
            <div class="text-2xl font-bold text-gray-900">₹{{ number_format($totalSales, 2) }}</div>
            <p class="text-xs text-gray-500 mt-1">Lifetime sales</p>
        </div>

        <!-- Card 2: Total Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Total Orders</h3>
                {{-- <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">+8.2%</span> --}}
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($totalOrders) }}</div>
            <p class="text-xs text-gray-500 mt-1">Lifetime orders</p>
        </div>

        <!-- Card 3: Active Products -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Active Products</h3>
                <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded">Live</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($activeProducts) }}</div>
            <p class="text-xs text-gray-500 mt-1">Currently listed</p>
        </div>

        <!-- Card 4: Pending Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Pending Orders</h3>
                @if($pendingOrders > 0)
                    <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded">Action Needed</span>
                @else
                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">All Clear</span>
                @endif
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($pendingOrders) }}</div>
            <p class="text-xs text-gray-500 mt-1">Requires processing</p>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Sales Overview (Last 6 Months)</h2>
        <div class="relative h-64 w-full">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Recent Orders (Takes up 2 cols) -->
        <div class="bg-white rounded-lg shadow-sm p-6 lg:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-gray-900">Recent Orders</h2>
                <a href="{{ route('seller.orders') }}" class="text-blue-600 text-sm font-medium hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Order ID</th>
                            <th class="px-4 py-3">Product(s)</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr class="border-b last:border-0 hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    <a href="{{ route('seller.orders.detail', $order->id) }}" class="hover:underline text-blue-600">
                                        #{{ $order->sub_order_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    @foreach($order->items as $item)
                                        <div class="truncate max-w-xs" title="{{ $item->product ? $item->product->name : $item->product_name }}">
                                            {{ $item->product ? $item->product->name : $item->product_name }}
                                            @if(!$loop->last), @endif
                                        </div>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3 text-gray-900">₹{{ number_format($order->total, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="{{ $order->status_badge_class }} px-2.5 py-0.5 rounded text-xs font-medium">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-center text-gray-500">No recent orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Selling Products (Takes up 1 col) -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-gray-900">Top Products</h2>
                <a href="{{ route('seller.products') }}" class="text-blue-600 text-sm font-medium hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                @forelse($topProducts as $item)
                    @if($item->product)
                    <div class="flex items-center gap-4">
                        <img src="{{ $item->product->main_image_url ?? 'https://placehold.co/50x50?text=No+Img' }}" 
                             class="w-10 h-10 rounded object-cover bg-gray-100"
                             alt="{{ $item->product->name }}">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-medium text-gray-900 truncate" title="{{ $item->product->name }}">
                                {{ $item->product->name }}
                            </h3>
                            <p class="text-xs text-gray-500">{{ $item->total_qty }} Sold</p>
                        </div>
                        <div class="font-bold text-gray-900 whitespace-nowrap">₹{{ number_format($item->total_revenue, 2) }}</div>
                    </div>
                    @endif
                @empty
                    <div class="text-center text-gray-500 text-sm py-4">No top products yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            const salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Total Sales (₹)',
                        data: @json($chartData),
                        borderColor: '#10B981', // Tailwind green-500
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.3, // Curve the line slightly
                        fill: true,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#10B981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [2, 4],
                                color: '#E5E7EB'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString('en-IN');
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush

</x-seller.layout>
