<x-marketplace.layout>
    @section('title', 'Contact Us')

    <div class="bg-gray-50 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Contact Info -->
                <div class="bg-white rounded-lg shadow-sm p-8">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Get in Touch</h1>
                    <p class="text-gray-600 mb-8">Have questions about your order or need help with our services? We're here to help!</p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">Visit Us</h3>
                                <p class="text-sm text-gray-600">Buildings Alyssa, Begonia & Clove Embassy Tech Village, Outer Ring Road, Devarabeesanahalli Village, Bengaluru, 560103</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">Call Us</h3>
                                <p class="text-sm text-gray-600">1800 202 9898</p>
                                <p class="text-xs text-gray-500">Mon-Sat, 9am - 6pm</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">Email Us</h3>
                                <p class="text-sm text-gray-600">support@xelnova.com</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="bg-white rounded-lg shadow-sm p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Send us a Message</h2>
                    <form class="space-y-4">
                        <x-ui.input name="name" label="Your Name" placeholder="Enter your name" />
                        <x-ui.input name="email" label="Email Address" type="email" placeholder="Enter your email" />
                        <x-ui.input name="subject" label="Subject" placeholder="Enter subject" />
                        
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" placeholder="How can we help you?"></textarea>
                        </div>

                        <x-ui.button fullWidth>Send Message</x-ui.button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-marketplace.layout>
