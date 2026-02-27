<x-admin.layout>
    @section('title', 'Sellers')

    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <input type="text" placeholder="Search sellers..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-xelnova-green-500 focus:border-xelnova-green-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 absolute left-3 top-3 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <select class="border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-xelnova-green-500 focus:border-xelnova-green-500">
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Pending</option>
                    <option>Suspended</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3">Seller</th>
                        <th class="px-6 py-3">Contact</th>
                        <th class="px-6 py-3">GSTIN</th>
                        <th class="px-6 py-3">Orders</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 1; $i <= 8; $i++)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600">S{{ $i }}</div>
                                    <div>
                                        <div class="font-medium text-gray-900">SuperComNet {{ $i }}</div>
                                        <div class="text-xs text-gray-500">Joined Oct 2025</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <div class="text-sm">seller{{ $i }}@example.com</div>
                                <div class="text-xs">+91 987654321{{ $i }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">29ABCDE1234F1Z{{ $i }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ rand(100, 1000) }}</td>
                            <td class="px-6 py-4">
                                @if($i % 3 == 0)
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Pending</span>
                                @else
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Active</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <button class="text-blue-600 hover:text-blue-800 font-medium text-xs">Manage</button>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t flex justify-between items-center">
            <span class="text-sm text-gray-500">Showing 1-8 of 24 sellers</span>
            <div class="flex gap-2">
                <button class="px-3 py-1 border rounded text-sm disabled:opacity-50" disabled>Previous</button>
                <button class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Next</button>
            </div>
        </div>
    </div>
</x-admin.layout>
