<x-marketplace.layout>
    @section('title', 'Frequently Asked Questions')

    <div class="bg-gray-50 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Frequently Asked Questions</h1>
                
                <div class="space-y-6" x-data="{ active: null }">
                    <!-- Q1 -->
                    <div class="border-b pb-4">
                        <button @click="active = active === 1 ? null : 1" class="flex justify-between items-center w-full text-left font-medium text-gray-900 hover:text-xelnova-green-600 focus:outline-none">
                            <span>How do I track my order?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 transition-transform" :class="{'rotate-180': active === 1}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="active === 1" x-collapse class="mt-2 text-gray-600 text-sm">
                            You can track your order by going to 'My Orders' in your account section. Click on the specific order to view its detailed tracking status.
                        </div>
                    </div>

                    <!-- Q2 -->
                    <div class="border-b pb-4">
                        <button @click="active = active === 2 ? null : 2" class="flex justify-between items-center w-full text-left font-medium text-gray-900 hover:text-xelnova-green-600 focus:outline-none">
                            <span>What is the return policy?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 transition-transform" :class="{'rotate-180': active === 2}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="active === 2" x-collapse class="mt-2 text-gray-600 text-sm">
                            We offer a 7-day return policy for most products. If you are not satisfied with your purchase, you can initiate a return from the 'My Orders' section within 7 days of delivery.
                        </div>
                    </div>

                    <!-- Q3 -->
                    <div class="border-b pb-4">
                        <button @click="active = active === 3 ? null : 3" class="flex justify-between items-center w-full text-left font-medium text-gray-900 hover:text-xelnova-green-600 focus:outline-none">
                            <span>How can I pay for my order?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 transition-transform" :class="{'rotate-180': active === 3}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="active === 3" x-collapse class="mt-2 text-gray-600 text-sm">
                            We accept various payment methods including Credit/Debit Cards, Net Banking, UPI, and Cash on Delivery (COD) for eligible pin codes.
                        </div>
                    </div>

                    <!-- Q4 -->
                    <div class="border-b pb-4">
                        <button @click="active = active === 4 ? null : 4" class="flex justify-between items-center w-full text-left font-medium text-gray-900 hover:text-xelnova-green-600 focus:outline-none">
                            <span>Can I cancel my order?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 transition-transform" :class="{'rotate-180': active === 4}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="active === 4" x-collapse class="mt-2 text-gray-600 text-sm">
                            Yes, you can cancel your order before it has been shipped. Go to 'My Orders' and select the order you wish to cancel.
                        </div>
                    </div>

                    <!-- Q5 -->
                    <div class="border-b pb-4">
                        <button @click="active = active === 5 ? null : 5" class="flex justify-between items-center w-full text-left font-medium text-gray-900 hover:text-xelnova-green-600 focus:outline-none">
                            <span>How do I become a seller on Xelnova?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 transition-transform" :class="{'rotate-180': active === 5}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="active === 5" x-collapse class="mt-2 text-gray-600 text-sm">
                            To become a seller, click on the 'Become a Seller' link in the footer or header. Fill out the registration form with your business details and start selling!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
