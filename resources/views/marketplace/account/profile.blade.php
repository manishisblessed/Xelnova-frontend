<x-marketplace.layout>
    @section('title', 'My Profile')

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
                                <p class="font-bold text-gray-900">{{ $user->name }}</p>
                            </div>
                        </div>
                        <nav class="space-y-1">
                            <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded">
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
                            <a href="{{ route('account.profile') }}" class="flex items-center gap-3 px-3 py-2 bg-blue-50 text-blue-600 rounded font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                My Profile
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="w-full lg:w-3/4 space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Personal Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <p class="text-gray-900">{{ $user->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <p class="text-gray-900">{{ $user->email ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <p class="text-gray-900">{{ $user->phone ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Member Since</label>
                                <p class="text-gray-900">{{ $user->created_at->format('d M, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Saved Addresses -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-bold text-gray-900">Saved Addresses</h2>
                        </div>

                        @if($addresses->isEmpty())
                            <p class="text-gray-500 text-center py-8">No saved addresses yet.</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($addresses as $address)
                                    <div class="border border-gray-200 rounded-lg p-4 {{ $address->is_default ? 'border-blue-500 bg-blue-50' : '' }}">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-gray-900">{{ $address->name }}</span>
                                                <span class="bg-gray-200 text-gray-600 text-[10px] px-1.5 py-0.5 rounded uppercase font-bold">{{ $address->type }}</span>
                                            </div>
                                            @if($address->is_default)
                                                <span class="bg-blue-600 text-white text-[10px] px-2 py-0.5 rounded uppercase font-bold">Default</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600 mb-2">
                                            {{ $address->address_line_1 }}<br>
                                            @if($address->address_line_2)
                                                {{ $address->address_line_2 }}<br>
                                            @endif
                                            {{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}
                                        </p>
                                        <p class="text-sm text-gray-600">Phone: {{ $address->phone }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
