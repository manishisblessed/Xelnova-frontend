<x-admin.layout>
    @section('title', 'Dashboard')

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">+18.2%</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">₹45,24,500</div>
            <p class="text-xs text-gray-500 mt-1">vs last month</p>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Active Sellers</h3>
                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">+5</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">124</div>
            <p class="text-xs text-gray-500 mt-1">Total Sellers</p>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Total Orders</h3>
                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">+12%</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">1,245</div>
            <p class="text-xs text-gray-500 mt-1">vs last month</p>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-500">Pending Approvals</h3>
                <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">Action Needed</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">8</div>
            <p class="text-xs text-gray-500 mt-1">Sellers & Products</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Recent Activity</h2>
            <div class="space-y-6">
                <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-900 font-medium">New Seller Registration</p>
                        <p class="text-xs text-gray-500">TechWorld Pvt Ltd requested to join.</p>
                        <p class="text-xs text-gray-400 mt-1">2 mins ago</p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-900 font-medium">New Order Placed</p>
                        <p class="text-xs text-gray-500">Order #OD123456789 placed by Sujit Kumar.</p>
                        <p class="text-xs text-gray-400 mt-1">15 mins ago</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-900 font-medium">Product Approval Request</p>
                        <p class="text-xs text-gray-500">SuperComNet added "Samsung Galaxy S24 Ultra".</p>
                        <p class="text-xs text-gray-400 mt-1">1 hour ago</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Sellers -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-gray-900">Top Sellers</h2>
                <a href="{{ route('admin.marketplace.sellers') }}" class="text-blue-600 text-sm font-medium hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                @for($i = 1; $i <= 5; $i++)
                    <div class="flex items-center gap-4">
                        <img src="https://placehold.co/50x50?text=S{{ $i }}" class="w-10 h-10 rounded-full bg-gray-100">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900">SuperComNet {{ $i }}</h3>
                            <p class="text-xs text-gray-500">1,245 Orders</p>
                        </div>
                        <div class="font-bold text-gray-900">₹45.2L</div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</x-admin.layout>
