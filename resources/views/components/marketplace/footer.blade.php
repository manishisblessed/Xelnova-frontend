<footer class="bg-gray-900 text-white pt-12 pb-6">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <!-- About -->
            <div>
                <h3 class="text-gray-400 text-sm font-semibold uppercase tracking-wider mb-4">About</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('contact') }}" class="hover:underline text-gray-300">Contact Us</a></li>
                    <li><a href="{{ route('about') }}" class="hover:underline text-gray-300">About Us</a></li>
                    <li><a href="{{ route('marketplace.page', ['slug' => 'careers']) }}" class="hover:underline text-gray-300">Careers</a></li>
                    <li><a href="{{ route('marketplace.page', ['slug' => 'press']) }}" class="hover:underline text-gray-300">Press</a></li>
                    <li><a href="{{ route('marketplace.page', ['slug' => 'corporate-information']) }}" class="hover:underline text-gray-300">Corporate Information</a></li>
                </ul>
            </div>

            <!-- Help -->
            <div>
                <h3 class="text-gray-400 text-sm font-semibold uppercase tracking-wider mb-4">Help</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('marketplace.page', ['slug' => 'payments']) }}" class="hover:underline text-gray-300">Payments</a></li>
                    <li><a href="{{ route('marketplace.page', ['slug' => 'shipping']) }}" class="hover:underline text-gray-300">Shipping</a></li>
                    <li><a href="{{ route('marketplace.page', ['slug' => 'cancellation-returns']) }}" class="hover:underline text-gray-300">Cancellation & Returns</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:underline text-gray-300">FAQ</a></li>
                    <li><a href="{{ route('marketplace.page', ['slug' => 'report-infringement']) }}" class="hover:underline text-gray-300">Report Infringement</a></li>
                </ul>
            </div>

            <!-- Policy -->
            <div>
                <h3 class="text-gray-400 text-sm font-semibold uppercase tracking-wider mb-4">Consumer Policy</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('returns') }}" class="hover:underline text-gray-300">Return Policy</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:underline text-gray-300">Terms of Use</a></li>
                    <li><a href="{{ route('marketplace.page', ['slug' => 'security']) }}" class="hover:underline text-gray-300">Security</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:underline text-gray-300">Privacy</a></li>
                    <li><a href="{{ route('marketplace.page', ['slug' => 'sitemap']) }}" class="hover:underline text-gray-300">Sitemap</a></li>
                </ul>
            </div>

            <!-- Social & Mail -->
            <div>
                <h3 class="text-gray-400 text-sm font-semibold uppercase tracking-wider mb-4">Social</h3>
                <div class="flex space-x-4 mb-6">
                    <a href="#" class="text-gray-300 hover:text-white transition">
                        <span class="sr-only">Facebook</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>
                    </a>
                    <a href="#" class="text-gray-300 hover:text-white transition">
                        <span class="sr-only">Twitter</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" /></svg>
                    </a>
                    <a href="#" class="text-gray-300 hover:text-white transition">
                        <span class="sr-only">YouTube</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.254.418-4.814a2.503 2.503 0 0 1 1.768-1.768C5.744 5 12 5 12 5s6.256 0 7.812.418zM15.194 12 10 15V9l5.194 3z" clip-rule="evenodd" /></svg>
                    </a>
                </div>
                
                <h3 class="text-gray-400 text-sm font-semibold uppercase tracking-wider mb-4">Registered Office</h3>
                <p class="text-xs text-gray-300 leading-relaxed">
                    XELNOVA PRIVATE LIMITED,<br>
                    Building No./Flat No.:122/1<br>
                    Road/Street:POLE NO- NEW LINE<br>
                    Nearby Landmark: MAHA LAXMI DHARAM KANTA<br>
                    Locality/SubLocality: VILL BAMNOLI, NAJAFGARH<br>
                    City/Town/Village: NEW DELHI<br>
                    District: SOUTH WEST DELHI<br>
                    State: DELHI<br>
                    Postal Code: 110077
                </p>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo-icon-white.png') }}" alt="Xelnova" class="h-8 w-auto">
                <span class="text-gray-400 text-sm">© 2025 Xelnova. All rights reserved.</span>
            </div>
            <div class="flex items-center gap-4">
                <img src="https://static-assets-web.flixcart.com/fk-p-linchpin-web/fk-cp-zion/img/payment-method_69e7ec.svg" alt="Payment Methods" class="h-6">
            </div>
        </div>
    </div>
</footer>
