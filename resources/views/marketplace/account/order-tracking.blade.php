<x-marketplace.layout>
    @section('title', 'Track Order')

    <div class="bg-gray-100 py-6">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 mb-1">Order ID: OD123456789012345</h1>
                        <p class="text-sm text-gray-500">Placed on Oct 21, 2025</p>
                    </div>
                    <a href="#" class="text-blue-600 font-medium text-sm hover:underline flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        Download Invoice
                    </a>
                </div>

                <div class="flex flex-col md:flex-row gap-6 border-t pt-6">
                    <div class="w-24 h-24 flex-shrink-0">
                        <img src="https://placehold.co/150x150?text=Phone" alt="Product" class="w-full h-full object-contain">
                    </div>
                    <div class="flex-1">
                        <h2 class="font-bold text-gray-900 mb-1">Samsung Galaxy S24 Ultra (Titanium Gray, 256GB)</h2>
                        <p class="text-sm text-gray-500 mb-2">Color: Titanium Gray</p>
                        <p class="text-sm text-gray-500 mb-4">Seller: SuperComNet</p>
                        <div class="font-bold text-lg text-gray-900">₹1,29,999</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Delivery Status</h2>
                
                <div class="relative pl-8 border-l-2 border-gray-200 space-y-8">
                    <!-- Step 1 -->
                    <div class="relative">
                        <div class="absolute -left-[41px] bg-green-500 rounded-full w-6 h-6 flex items-center justify-center text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">Order Placed</h3>
                        <p class="text-sm text-gray-500">Oct 21, 2025, 10:30 AM</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative">
                        <div class="absolute -left-[41px] bg-green-500 rounded-full w-6 h-6 flex items-center justify-center text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">Packed</h3>
                        <p class="text-sm text-gray-500">Oct 21, 2025, 02:15 PM</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative">
                        <div class="absolute -left-[41px] bg-green-500 rounded-full w-6 h-6 flex items-center justify-center text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">Shipped</h3>
                        <p class="text-sm text-gray-500">Oct 21, 2025, 06:45 PM</p>
                        <p class="text-xs text-gray-400 mt-1">Courier: E-Kart Logistics | Tracking ID: FMPC123456789</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="relative">
                        <div class="absolute -left-[41px] bg-blue-600 rounded-full w-6 h-6 flex items-center justify-center text-white ring-4 ring-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125v-3.75m16.5-1.5-.904-3.616a2.25 2.25 0 0 0-2.182-1.734h-6.414a2.25 2.25 0 0 0-2.182 1.734L4.875 10.5m16.5 0V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V10.5m16.5 0V15" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">Out for Delivery</h3>
                        <p class="text-sm text-gray-500">Expected Today</p>
                    </div>

                    <!-- Step 5 -->
                    <div class="relative">
                        <div class="absolute -left-[41px] bg-gray-200 rounded-full w-6 h-6 flex items-center justify-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-400">Delivered</h3>
                        <p class="text-sm text-gray-400">Expected by Oct 24</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Shipping Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-bold text-gray-700 mb-2">Delivery Address</h3>
                        <p class="text-sm text-gray-600 font-bold">Sujit Kumar</p>
                        <p class="text-sm text-gray-600">Buildings Alyssa, Begonia & Clove Embassy Tech Village, Outer Ring Road, Devarabeesanahalli Village</p>
                        <p class="text-sm text-gray-600">Bengaluru, Karnataka - 560103</p>
                        <p class="text-sm text-gray-600 font-bold mt-1">Phone: 9876543210</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-700 mb-2">Payment Information</h3>
                        <p class="text-sm text-gray-600">Payment Method: <span class="font-medium">Credit Card</span></p>
                        <p class="text-sm text-gray-600">Status: <span class="text-green-600 font-medium">Paid</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
