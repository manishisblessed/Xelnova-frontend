<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Seller Portal - {{ config('app.name', 'Xelnova') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/frontend.css', 'resources/js/marketplace.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 h-screen flex overflow-hidden" x-data="{ sidebarOpen: false }">
    
    <!-- Sidebar -->
    <aside class="bg-gray-900 text-white w-64 flex-shrink-0 hidden md:flex flex-col transition-all duration-300">
        <div class="h-16 flex items-center px-6 border-b border-gray-800">
            <span class="text-xl font-bold text-xelnova-gold-500">Xelnova Seller</span>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-2">
                <li>
                    <a href="{{ route('seller.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('seller.dashboard') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('seller.products') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('seller.products*') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        Products
                    </a>
                </li>
                <li>
                    <a href="{{ route('seller.orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('seller.orders') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125v-3.75m16.5-1.5-.904-3.616a2.25 2.25 0 00-2.182-1.734h-6.414a2.25 2.25 0 00-2.182 1.734L4.875 10.5m16.5 0V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V10.5m16.5 0V15" />
                        </svg>
                        Orders
                    </a>
                </li>
                <li>
                    <a href="{{ route('seller.finance') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('seller.finance') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Finance
                    </a>
                </li>
                <li>
                    <a href="{{ route('seller.documents') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('seller.documents') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        Documents
                    </a>
                </li>
                <li>
                    <a href="{{ route('seller.bank-accounts') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('seller.bank-accounts') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                        Bank Accounts
                    </a>
                </li>
                <li>
                    <a href="{{ route('seller.brands') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('seller.brands*') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                        Brands
                    </a>
                </li>
                <li>
                    <a href="{{ route('seller.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 {{ request()->routeIs('seller.settings') ? 'bg-xelnova-green-600 text-white' : 'text-gray-400' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </a>
                </li>
            </ul>

        </nav>
        
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-xelnova-green-600 flex items-center justify-center text-white font-bold">
                    {{ substr(auth()->user()->name ?? 'S', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate">{{ auth()->user()->name ?? 'Seller' }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ auth()->user()->email ?? '' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('seller.logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition">
                    Logout
                </button>
            </form>
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
                <button class="text-gray-500 hover:text-gray-700 relative">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <a href="{{ route('home') }}" class="text-sm text-blue-600 hover:underline">Back to Marketplace</a>
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('message') || session('error') || session('success'))
            <div class="px-6 pt-4">
                @if(session('success') || session('message'))
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-start gap-3" x-data="{ show: true }" x-show="show">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">{{ session('success') ?? session('message') }}</div>
                        <button @click="show = false" class="text-green-600 hover:text-green-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-start gap-3" x-data="{ show: true }" x-show="show">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">{{ session('error') }}</div>
                        <button @click="show = false" class="text-red-600 hover:text-red-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        @endif

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
            <span class="text-xl font-bold text-xelnova-gold-500">Xelnova Seller</span>
            <button @click="sidebarOpen = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-2">
                <li><a href="{{ route('seller.dashboard') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-800">Dashboard</a></li>
                <li><a href="{{ route('seller.products') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-800">Products</a></li>
                <li><a href="{{ route('seller.orders') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-800">Orders</a></li>
                <li><a href="{{ route('seller.finance') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-800">Finance</a></li>
                <li><a href="{{ route('seller.documents') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-800">Documents</a></li>
                <li><a href="{{ route('seller.bank-accounts') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-800">Bank Accounts</a></li>
                <li><a href="{{ route('seller.brands') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-800">Brands</a></li>
            </ul>
        </nav>

    </div>

    @stack('scripts')
</body>
</html>
