<x-marketplace.layout>
    @section('title', 'Forgot Password')

    <div class="bg-gray-50 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white p-8 text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold mb-2">Forgot Password?</h1>
                    <p class="text-gray-300">No worries, we'll send you reset instructions</p>
                </div>
                
                <div class="p-8">
                    @if(session('status'))
                        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('seller.password.email') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Enter your registered email">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            <p class="mt-1 text-xs text-gray-500">We'll send a password reset link to this email</p>
                        </div>
                        
                        <button type="submit" 
                            class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            Send Reset Link
                        </button>
                        
                        <div class="text-center">
                            <a href="{{ route('seller.login') }}" class="text-sm text-blue-600 hover:underline flex items-center justify-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
