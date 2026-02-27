<x-marketplace.layout>
    @section('title', 'Sell on Xelnova - Join Our Marketplace')

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-bold mb-6">
                    Grow Your Business with <span class="text-green-400">Xelnova</span>
                </h1>
                <p class="text-xl md:text-2xl text-gray-300 mb-8">
                    Reach millions of customers across India and scale your business with our trusted marketplace
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('seller.register') }}" 
                        class="px-8 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-bold text-lg shadow-lg">
                        Start Selling Now
                    </a>
                    <a href="{{ route('seller.login') }}" 
                        class="px-8 py-4 bg-white text-gray-900 rounded-lg hover:bg-gray-100 transition font-bold text-lg shadow-lg">
                        Seller Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-5xl mx-auto">
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-600 mb-2">10M+</div>
                    <div class="text-gray-600">Active Customers</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-600 mb-2">50K+</div>
                    <div class="text-gray-600">Sellers</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-600 mb-2">500+</div>
                    <div class="text-gray-600">Cities</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-600 mb-2">24/7</div>
                    <div class="text-gray-600">Support</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Sell on Xelnova?</h2>
                <p class="text-xl text-gray-600">Everything you need to succeed online</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Benefit 1 -->
                <div class="bg-white rounded-xl shadow-sm p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Massive Customer Base</h3>
                    <p class="text-gray-600">Access millions of active buyers across India looking for products like yours</p>
                </div>

                <!-- Benefit 2 -->
                <div class="bg-white rounded-xl shadow-sm p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Low Commission</h3>
                    <p class="text-gray-600">Competitive commission rates to maximize your profits and grow faster</p>
                </div>

                <!-- Benefit 3 -->
                <div class="bg-white rounded-xl shadow-sm p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Secure Payments</h3>
                    <p class="text-gray-600">Timely payouts with secure payment processing and fraud protection</p>
                </div>

                <!-- Benefit 4 -->
                <div class="bg-white rounded-xl shadow-sm p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Easy to Start</h3>
                    <p class="text-gray-600">Simple registration process and intuitive seller dashboard to manage everything</p>
                </div>

                <!-- Benefit 5 -->
                <div class="bg-white rounded-xl shadow-sm p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Logistics Support</h3>
                    <p class="text-gray-600">Integrated shipping partners for hassle-free delivery and returns</p>
                </div>

                <!-- Benefit 6 -->
                <div class="bg-white rounded-xl shadow-sm p-8 hover:shadow-lg transition">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Marketing Tools</h3>
                    <p class="text-gray-600">Promotional campaigns, featured listings, and analytics to boost sales</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-xl text-gray-600">Start selling in 4 simple steps</p>
            </div>
            
            <div class="max-w-5xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Step 1 -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Register</h3>
                        <p class="text-gray-600 text-sm">Create your seller account with business details</p>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Verify</h3>
                        <p class="text-gray-600 text-sm">Upload documents and complete verification</p>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">List Products</h3>
                        <p class="text-gray-600 text-sm">Add your products with images and descriptions</p>
                    </div>
                    
                    <!-- Step 4 -->
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">4</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Start Selling</h3>
                        <p class="text-gray-600 text-sm">Receive orders and grow your business</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
            </div>
            
            <div class="max-w-3xl mx-auto space-y-4" x-data="{ openFaq: null }">
                <!-- FAQ 1 -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <button @click="openFaq = openFaq === 1 ? null : 1" 
                        class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition">
                        <span class="font-medium text-gray-900">What are the fees to sell on Xelnova?</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 1" x-cloak class="px-6 pb-4 text-gray-600">
                        We charge a competitive commission on each sale, which varies by category. There are no listing fees or monthly subscription charges.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <button @click="openFaq = openFaq === 2 ? null : 2" 
                        class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition">
                        <span class="font-medium text-gray-900">How do I receive payments?</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 2" x-cloak class="px-6 pb-4 text-gray-600">
                        Payments are transferred directly to your registered bank account on a weekly basis after successful delivery of orders.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <button @click="openFaq = openFaq === 3 ? null : 3" 
                        class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition">
                        <span class="font-medium text-gray-900">What documents do I need to register?</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 3" x-cloak class="px-6 pb-4 text-gray-600">
                        You'll need PAN card, GST certificate (if applicable), business registration documents, address proof, and bank account details.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <button @click="openFaq = openFaq === 4 ? null : 4" 
                        class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition">
                        <span class="font-medium text-gray-900">How long does verification take?</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 4" x-cloak class="px-6 pb-4 text-gray-600">
                        Verification typically takes 2-3 business days after you submit all required documents. You'll be notified via email once approved.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-green-600 to-green-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">Ready to Start Selling?</h2>
            <p class="text-xl mb-8 text-green-100">Join thousands of successful sellers on Xelnova today</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('seller.register') }}" 
                    class="px-8 py-4 bg-white text-green-600 rounded-lg hover:bg-gray-100 transition font-bold text-lg shadow-lg">
                    Register Now - It's Free
                </a>
                <a href="{{ route('contact') }}" 
                    class="px-8 py-4 bg-green-800 text-white rounded-lg hover:bg-green-900 transition font-bold text-lg shadow-lg">
                    Contact Sales Team
                </a>
            </div>
        </div>
    </section>

</x-marketplace.layout>
