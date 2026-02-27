<x-marketplace.layout>
    @section('title', 'Seller Login')

    <div class="bg-gray-50 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white p-8 text-center">
                    <h1 class="text-2xl font-bold mb-2">Seller Login</h1>
                    <p class="text-gray-300">Access your seller dashboard</p>
                </div>
                
                <div class="p-8">
                    @if(session('status'))
                        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('seller.login.post') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="your@email.com">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Enter your password">
                            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember" class="text-green-600 focus:ring-green-500 rounded">
                                <span class="text-sm text-gray-600">Remember me</span>
                            </label>
                            <a href="{{ route('seller.password.request') }}" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                        </div>
                        
                        <button type="submit" 
                            class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            Login to Dashboard
                        </button>
                        
                        <div class="text-center text-sm text-gray-500">
                            Don't have a seller account? <a href="{{ route('seller.register') }}" class="text-blue-600 font-medium hover:underline">Register now</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
