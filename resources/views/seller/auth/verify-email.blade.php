<x-marketplace.layout>
    @section('title', 'Verify Email')

    <div class="bg-gray-50 py-12" x-data="verificationForm()">
        <div class="container mx-auto px-4">
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white p-8 text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold mb-2">Verify Your Email</h1>
                    <p class="text-gray-300">We've sent a 6-digit code to your email</p>
                </div>
                
                <div class="p-8">
                    @if(session('message'))
                        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                            {{ session('message') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('seller.verify-email.post') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-center">Enter Verification Code</label>
                            <div class="flex gap-2 justify-center">
                                <input type="text" maxlength="1" x-ref="digit1" @input="moveToNext($event, $refs.digit2)" 
                                    class="w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <input type="text" maxlength="1" x-ref="digit2" @input="moveToNext($event, $refs.digit3)" 
                                    class="w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <input type="text" maxlength="1" x-ref="digit3" @input="moveToNext($event, $refs.digit4)" 
                                    class="w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <input type="text" maxlength="1" x-ref="digit4" @input="moveToNext($event, $refs.digit5)" 
                                    class="w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <input type="text" maxlength="1" x-ref="digit5" @input="moveToNext($event, $refs.digit6)" 
                                    class="w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <input type="text" maxlength="1" x-ref="digit6" @input="updateCode()" 
                                    class="w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            <input type="hidden" name="code" x-model="code">
                            @error('code')<p class="mt-2 text-sm text-red-600 text-center">{{ $message }}</p>@enderror
                        </div>
                        
                        <button type="submit" 
                            class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            Verify Email
                        </button>
                    </form>
                    
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600 mb-2">Didn't receive the code?</p>
                        <form method="POST" action="{{ route('seller.resend-verification') }}" x-show="!resending">
                            @csrf
                            <button type="submit" @click="startResendTimer()" 
                                class="text-blue-600 hover:underline font-medium text-sm"
                                :disabled="countdown > 0">
                                <span x-show="countdown === 0">Resend Code</span>
                                <span x-show="countdown > 0">Resend in <span x-text="countdown"></span>s</span>
                            </button>
                        </form>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t text-center">
                        <p class="text-xs text-gray-500">
                            The verification code will expire in 5 minutes
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function verificationForm() {
            return {
                code: '',
                countdown: 0,
                resending: false,
                
                moveToNext(event, nextRef) {
                    if (event.target.value.length === 1 && nextRef) {
                        nextRef.focus();
                    }
                    this.updateCode();
                },
                
                updateCode() {
                    this.code = this.$refs.digit1.value + 
                                this.$refs.digit2.value + 
                                this.$refs.digit3.value + 
                                this.$refs.digit4.value + 
                                this.$refs.digit5.value + 
                                this.$refs.digit6.value;
                },
                
                startResendTimer() {
                    this.countdown = 60;
                    this.resending = true;
                    const interval = setInterval(() => {
                        this.countdown--;
                        if (this.countdown === 0) {
                            clearInterval(interval);
                            this.resending = false;
                        }
                    }, 1000);
                }
            }
        }
    </script>
</x-marketplace.layout>
