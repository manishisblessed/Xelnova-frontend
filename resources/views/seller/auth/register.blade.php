<x-marketplace.layout>
    @section('title', 'Seller Registration')

    <div class="bg-gray-50 py-12" x-data="registrationForm()">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white p-8 text-center">
                    <h1 class="text-3xl font-bold mb-2">Start Selling on Xelnova</h1>
                    <p class="text-gray-300">Reach millions of customers across India</p>
                </div>
                
                <!-- Progress Steps -->
                <div class="bg-gray-50 px-8 py-4">
                    <div class="flex items-center justify-between max-w-2xl mx-auto">
                        <div class="flex items-center flex-1">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold transition" :class="step >= 1 ? 'bg-green-600' : 'bg-gray-300'">1</div>
                            <div class="flex-1 h-1 mx-2 transition" :class="step >= 2 ? 'bg-green-600' : 'bg-gray-300'"></div>
                        </div>
                        <div class="flex items-center flex-1">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold transition" :class="step >= 2 ? 'bg-green-600' : 'bg-gray-300'">2</div>
                            <div class="flex-1 h-1 mx-2 transition" :class="step >= 3 ? 'bg-green-600' : 'bg-gray-300'"></div>
                        </div>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold transition" :class="step >= 3 ? 'bg-green-600' : 'bg-gray-300'">3</div>
                    </div>
                    <div class="flex justify-between max-w-2xl mx-auto mt-2 text-xs text-gray-600">
                        <span class="flex-1 text-center">Account</span>
                        <span class="flex-1 text-center">Business</span>
                        <span class="flex-1 text-center">Verification</span>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('seller.register.post') }}" class="p-8">
                    @csrf
                    
                    <!-- Step 1: Account Information -->
                    <div x-show="step === 1" x-cloak>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Create Your Account</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Enter your full name">
                                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="your@email.com">
                                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                                    <input type="password" name="password" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Create password">
                                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                                    <input type="password" name="password_confirmation" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Confirm password">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 2: Business Information -->
                    <div x-show="step === 2" x-cloak>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Business Details</h2>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Name *</label>
                                    <input type="text" name="business_name" value="{{ old('business_name') }}" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Your business name">
                                    @error('business_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Type *</label>
                                    <select name="business_type" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="">Select type</option>
                                        <option value="individual" {{ old('business_type') == 'individual' ? 'selected' : '' }}>Individual</option>
                                        <option value="company" {{ old('business_type') == 'company' ? 'selected' : '' }}>Company</option>
                                        <option value="partnership" {{ old('business_type') == 'partnership' ? 'selected' : '' }}>Partnership</option>
                                    </select>
                                    @error('business_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Business Registration Number</label>
                                <input type="text" name="business_registration_number" value="{{ old('business_registration_number') }}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Optional">
                                @error('business_registration_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Business Address *</label>
                                <textarea name="business_address" required rows="3" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Complete business address">{{ old('business_address') }}</textarea>
                                @error('business_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                                    <input type="text" name="city" value="{{ old('city') }}" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">State *</label>
                                    <input type="text" name="state" value="{{ old('state') }}" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('state')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code *</label>
                                    <input type="text" name="postal_code" value="{{ old('postal_code') }}" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('postal_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Country *</label>
                                    <input type="text" name="country" value="{{ old('country', 'India') }}" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    @error('country')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                    <input type="tel" name="phone" value="{{ old('phone') }}" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="+91 9876543210">
                                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Business Email *</label>
                                <input type="email" name="seller_email" value="{{ old('seller_email') }}" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="business@example.com">
                                @error('seller_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 3: Tax Information -->
                    <div x-show="step === 3" x-cloak>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Tax Information</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">GST Number</label>
                                <input type="text" name="gst_number" value="{{ old('gst_number') }}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="22AAAAA0000A1Z5">
                                <p class="mt-1 text-xs text-gray-500">Optional - You can add this later</p>
                                @error('gst_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">PAN Number</label>
                                <input type="text" name="pan_number" value="{{ old('pan_number') }}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="ABCDE1234F">
                                <p class="mt-1 text-xs text-gray-500">Optional - You can add this later</p>
                                @error('pan_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                                <div class="flex items-start gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="text-sm text-blue-800">
                                        <p class="font-medium mb-1">Next Steps After Registration:</p>
                                        <ul class="list-disc list-inside space-y-1 text-blue-700">
                                            <li>Verify your email address</li>
                                            <li>Upload required documents (PAN, GST, etc.)</li>
                                            <li>Add bank account details</li>
                                            <li>Wait for admin approval</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="pt-4">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" required class="mt-1 text-green-600 focus:ring-green-500 rounded">
                                    <span class="text-sm text-gray-600">I agree to Xelnova's <a href="#" class="text-blue-600 hover:underline">Seller Terms & Conditions</a> and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>.</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex justify-between items-center mt-8 pt-6 border-t">
                        <button type="button" @click="step--" x-show="step > 1" 
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Previous
                        </button>
                        <div x-show="step === 1"></div>
                        
                        <button type="button" @click="step++" x-show="step < 3" 
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Next Step
                        </button>
                        
                        <button type="submit" x-show="step === 3" 
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            Complete Registration
                        </button>
                    </div>
                    
                    <div class="text-center mt-6 text-sm text-gray-500">
                        Already have a seller account? <a href="{{ route('seller.login') }}" class="text-blue-600 font-medium hover:underline">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function registrationForm() {
            return {
                step: 1
            }
        }
    </script>
</x-marketplace.layout>
