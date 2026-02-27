<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Xelnova') }} - @yield('title', 'Online Shopping Site')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/frontend.css', 'resources/js/marketplace.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900 flex flex-col min-h-screen">
    
    <x-marketplace.header />

    <main class="flex-grow">
        {{ $slot }}
    </main>

    <x-marketplace.footer />

    <!-- Toast Notification -->
    <div x-data="toastNotification()" 
         x-show="show" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
         x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
         class="fixed top-20 right-4 z-50 max-w-sm w-full shadow-lg rounded-lg pointer-events-auto">
        <div class="rounded-lg shadow-lg overflow-hidden">
            <div class="p-4" :class="type === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg x-show="type === 'success'" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="type === 'error'" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium" :class="type === 'success' ? 'text-green-800' : 'text-red-800'" x-text="message"></p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button @click="hide" class="inline-flex rounded-md p-1.5 focus:outline-none" :class="type === 'success' ? 'text-green-500 hover:bg-green-100' : 'text-red-500 hover:bg-red-100'">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toastNotification() {
            return {
                show: false,
                message: '',
                type: 'success',
                timeout: null,

                init() {
                    window.addEventListener('show-toast', (event) => {
                        this.message = event.detail.message;
                        this.type = event.detail.type || 'success';
                        this.show = true;

                        // Auto hide after 3 seconds
                        clearTimeout(this.timeout);
                        this.timeout = setTimeout(() => {
                            this.hide();
                        }, 3000);
                    });
                },

                hide() {
                    this.show = false;
                }
            };
        }
    </script>

</body>
</html>
