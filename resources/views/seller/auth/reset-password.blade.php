<x-marketplace.layout>
    @section('title', 'Reset Password')

    <div class="bg-gray-50 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white p-8 text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold mb-2">Reset Password</h1>
                    <p class="text-gray-300">Enter your new password</p>
                </div>
                
                <div class="p-8">
                    <form method="POST" action="{{ route('seller.password.update') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="password" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Enter new password">
                            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Confirm new password">
                        </div>
                        
                        <button type="submit" 
                            class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
