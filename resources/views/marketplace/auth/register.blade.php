<x-marketplace.layout>
    @section('title', 'Create Account')

    <div class="bg-gray-100 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm overflow-hidden flex flex-col md:flex-row min-h-[550px]" 
                 x-data="registerForm()">
                <!-- Left Side -->
                <div class="bg-xelnova-green-600 text-white p-8 md:w-2/5 flex flex-col justify-between relative overflow-hidden">
                    <div class="relative z-10">
                        <h2 class="text-3xl font-bold mb-4">Looks like you're new here!</h2>
                        <p class="text-lg text-blue-100">Sign up with your email or mobile number to get started</p>
                    </div>
                    <div class="relative z-10 text-center">
                        <img src="{{ asset('images/logo-icon-white.png') }}" alt="Logo" class="h-20 mx-auto opacity-80">
                    </div>
                    <!-- Decorative Circle -->
                    <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-white opacity-10 rounded-full"></div>
                    <div class="absolute -top-20 -right-20 w-60 h-60 bg-white opacity-10 rounded-full"></div>
                </div>

                <!-- Right Side -->
                <div class="p-8 md:w-3/5 flex flex-col justify-center">
                    <!-- Step 1: Enter Details -->
                    <form x-show="step === 1" @submit.prevent="sendOtp" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                            <input 
                                type="text" 
                                x-model="name"
                                placeholder="Enter your full name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-xelnova-green-500 focus:border-xelnova-green-500 transition"
                                :disabled="loading"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email or Mobile Number</label>
                            <input 
                                type="text" 
                                x-model="identifier"
                                placeholder="Email or 10-digit mobile number"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-xelnova-green-500 focus:border-xelnova-green-500 transition"
                                :disabled="loading"
                                required>
                        </div>
                        
                        <div class="text-xs text-gray-500">
                            By continuing, you agree to Xelnova's <a href="{{ route('terms') }}" class="text-blue-600">Terms of Use</a> and <a href="{{ route('privacy') }}" class="text-blue-600">Privacy Policy</a>.
                        </div>

                        <!-- Error Message -->
                        <div x-show="error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm" x-text="error"></div>
                        
                        <button type="submit" 
                                :disabled="loading || !identifier || !name"
                                class="w-full bg-xelnova-gold-500 hover:bg-xelnova-gold-600 text-white font-bold py-3 px-6 rounded-lg shadow-sm transition uppercase text-sm disabled:opacity-70 flex items-center justify-center gap-2">
                            <svg x-show="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="loading ? 'Sending...' : 'Continue'"></span>
                        </button>
                        
                        <div class="text-center">
                            <a href="{{ route('customer.login') }}" class="text-blue-600 font-medium text-sm hover:underline">Existing User? Log in</a>
                        </div>
                    </form>

                    <!-- Step 2: Enter OTP -->
                    <form x-show="step === 2" @submit.prevent="verifyOtp" class="space-y-6" x-cloak>
                        <div class="text-center mb-4">
                            <p class="text-gray-600">Please enter the OTP sent to</p>
                            <p class="font-bold text-gray-800" x-text="identifier"></p>
                            <button type="button" @click="step = 1; error = ''" class="text-blue-600 text-sm hover:underline mt-1">Change</button>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-center">Enter 6-digit OTP</label>
                            <div class="flex gap-2 justify-center">
                                <template x-for="(digit, index) in otpDigits" :key="index">
                                    <input 
                                        type="text" 
                                        maxlength="1"
                                        x-model="otpDigits[index]"
                                        @input="handleOtpInput($event, index)"
                                        @keydown="handleOtpKeydown($event, index)"
                                        @paste="handleOtpPaste($event)"
                                        :id="'otp-' + index"
                                        class="w-12 h-14 text-center text-xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-xelnova-green-500 focus:border-xelnova-green-500 transition"
                                        :disabled="loading">
                                </template>
                            </div>
                        </div>

                        <!-- Timer and Resend -->
                        <div class="text-center text-sm">
                            <template x-if="resendTimer > 0">
                                <span class="text-gray-500">Resend OTP in <span x-text="resendTimer" class="font-bold"></span>s</span>
                            </template>
                            <template x-if="resendTimer <= 0">
                                <button type="button" @click="sendOtp" class="text-blue-600 hover:underline font-medium">Resend OTP</button>
                            </template>
                        </div>

                        <!-- Error Message -->
                        <div x-show="error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm" x-text="error"></div>

                        <!-- Debug OTP (development only) -->
                        @if(config('app.debug'))
                        <div x-show="debugOtp" class="bg-yellow-50 text-yellow-800 p-3 rounded-lg text-sm text-center">
                            Debug OTP: <span x-text="debugOtp" class="font-bold"></span>
                        </div>
                        @endif
                        
                        <button type="submit" 
                                :disabled="loading || otp.length !== 6"
                                class="w-full bg-xelnova-green-600 hover:bg-xelnova-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-sm transition uppercase text-sm disabled:opacity-70 flex items-center justify-center gap-2">
                            <svg x-show="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="loading ? 'Creating Account...' : 'Verify & Create Account'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function registerForm() {
            return {
                step: 1,
                name: '',
                identifier: '',
                identifierType: 'email',
                otpDigits: ['', '', '', '', '', ''],
                remember: true, // Auto-enable remember me for new users
                loading: false,
                error: '',
                resendTimer: 0,
                debugOtp: null,

                get otp() {
                    return this.otpDigits.join('');
                },

                detectIdentifierType() {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    const phoneRegex = /^[0-9]{10}$/;
                    
                    if (emailRegex.test(this.identifier)) {
                        this.identifierType = 'email';
                        return true;
                    } else if (phoneRegex.test(this.identifier)) {
                        this.identifierType = 'phone';
                        return true;
                    }
                    return false;
                },

                async sendOtp() {
                    if (!this.name.trim()) {
                        this.error = 'Please enter your name';
                        return;
                    }

                    if (!this.detectIdentifierType()) {
                        this.error = 'Please enter a valid email or 10-digit mobile number';
                        return;
                    }

                    this.loading = true;
                    this.error = '';

                    try {
                        const response = await fetch('/customer/send-otp', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                identifier: this.identifier,
                                type: this.identifierType
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.step = 2;
                            this.startResendTimer();
                            this.debugOtp = data.debug_otp;
                            this.$nextTick(() => {
                                document.getElementById('otp-0')?.focus();
                            });
                        } else {
                            this.error = data.message || 'Failed to send OTP';
                        }
                    } catch (error) {
                        console.error('Error sending OTP:', error);
                        this.error = 'Failed to send OTP. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                async verifyOtp() {
                    if (this.otp.length !== 6) {
                        this.error = 'Please enter the complete 6-digit OTP';
                        return;
                    }

                    this.loading = true;
                    this.error = '';

                    try {
                        const response = await fetch('/customer/verify-otp', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                identifier: this.identifier,
                                type: this.identifierType,
                                otp: this.otp,
                                name: this.name,
                                remember: this.remember
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.href = data.redirect || '/';
                        } else {
                            this.error = data.message || 'Invalid OTP';
                            this.otpDigits = ['', '', '', '', '', ''];
                            this.$nextTick(() => {
                                document.getElementById('otp-0')?.focus();
                            });
                        }
                    } catch (error) {
                        console.error('Error verifying OTP:', error);
                        this.error = 'Failed to verify OTP. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                startResendTimer() {
                    this.resendTimer = 30;
                    const interval = setInterval(() => {
                        this.resendTimer--;
                        if (this.resendTimer <= 0) {
                            clearInterval(interval);
                        }
                    }, 1000);
                },

                handleOtpInput(event, index) {
                    const value = event.target.value;
                    if (value && index < 5) {
                        document.getElementById('otp-' + (index + 1))?.focus();
                    }
                },

                handleOtpKeydown(event, index) {
                    if (event.key === 'Backspace' && !this.otpDigits[index] && index > 0) {
                        document.getElementById('otp-' + (index - 1))?.focus();
                    }
                },

                handleOtpPaste(event) {
                    const paste = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                    if (paste.length === 6) {
                        this.otpDigits = paste.split('');
                        document.getElementById('otp-5')?.focus();
                    }
                    event.preventDefault();
                }
            };
        }
    </script>
</x-marketplace.layout>
