<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Portal - {{ config('app.name', 'Xelnova') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/main.css', 'resources/js/marketplace.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 h-screen flex overflow-hidden" x-data="{ sidebarOpen: false }">
    
    <!-- Sidebar -->
    <aside class="bg-gray-900 text-white w-64 flex-shrink-0 hidden md:flex flex-col transition-all duration-300">
        <div class="h-16 flex items-center px-6 border-b border-gray-800">
            <span class="text-xl font-bold text-xelnova-gold-500">Xelnova Admin</span>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-2">
                <li>
                    <a href="{{ route('admin.marketplace.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('admin.marketplace.dashboard') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.marketplace.sellers') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('admin.marketplace.sellers') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        Sellers
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.marketplace.products') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('admin.marketplace.products') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        Products
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3">
                <img src="https://placehold.co/40x40?text=A" class="w-10 h-10 rounded-full bg-gray-700">
                <div>
                    <div class="text-sm font-medium">Admin User</div>
                    <div class="text-xs text-gray-500">Super Admin</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6">
            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            
            <h1 class="text-xl font-bold text-gray-800">@yield('title')</h1>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="text-sm text-blue-600 hover:underline">Back to Marketplace</a>
            </div>
        </header>

        <!-- Content Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            {{ $slot }}
        </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-50 bg-gray-900 bg-opacity-50 md:hidden" @click="sidebarOpen = false" x-cloak></div>
    
    <!-- Mobile Sidebar -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white md:hidden" x-cloak>
         <!-- Same sidebar content as desktop -->
         <div class="h-16 flex items-center px-6 border-b border-gray-800 justify-between">
            <span class="text-xl font-bold text-xelnova-gold-500">Xelnova Admin</span>
            <button @click="sidebarOpen = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-2">
                <li><a href="{{ route('admin.marketplace.dashboard') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-800">Dashboard</a></li>
                <li><a href="{{ route('admin.marketplace.sellers') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-800">Sellers</a></li>
                <li><a href="{{ route('admin.marketplace.products') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-800">Products</a></li>
            </ul>
        </nav>
    </div>

</body>
</html>
