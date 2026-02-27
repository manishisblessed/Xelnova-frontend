<x-marketplace.layout>
    @section('title', 'Checkout')

    <div class="bg-gray-100 py-6">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-6" x-data="checkoutPage({{ Js::from($addresses) }}, {{ Js::from($cartSummary) }})">
                <!-- Checkout Steps -->
                <div class="w-full lg:w-2/3 space-y-4">
                    
                    <!-- Step 1: Login -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 flex justify-between items-center bg-blue-50">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <h3 class="font-bold text-gray-800 uppercase text-sm">Login</h3>
                                <span class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Delivery Address -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 bg-blue-50 flex items-center gap-3">
                            <span class="bg-blue-600 text-white font-bold px-2 py-0.5 text-xs rounded">2</span>
                            <h3 class="font-bold text-gray-800 uppercase text-sm">Delivery Address</h3>
                        </div>
                        
                        <div class="p-4">
                            <!-- Existing Addresses -->
                            <div class="space-y-4 mb-4" x-show="addresses.length > 0" x-cloak>
                                <template x-for="address in addresses" :key="address.id">
                                    <label class="flex items-start gap-3 p-4 border rounded cursor-pointer transition"
                                           :class="selectedAddressId === address.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                        <input type="radio" 
                                               name="address" 
                                               :value="address.id"
                                               x-model="selectedAddressId"
                                               class="mt-1 text-blue-600 focus:ring-blue-500">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-bold text-gray-800" x-text="address.name"></span>
                                                <span class="bg-gray-200 text-gray-600 text-[10px] px-1.5 py-0.5 rounded uppercase font-bold" x-text="address.type"></span>
                                                <span class="font-bold text-gray-800 ml-2" x-text="address.phone"></span>
                                            </div>
                                            <p class="text-sm text-gray-600" x-text="address.full_address"></p>
                                        </div>
                                    </label>
                                </template>
                            </div>

                            <!-- Add New Address Button -->
                            <button @click="showAddressForm = !showAddressForm" 
                                    class="w-full border-2 border-dashed border-gray-300 hover:border-blue-500 text-blue-600 font-bold py-3 rounded text-sm uppercase transition"
                                    x-text="showAddressForm ? 'Cancel' : '+ Add New Address'">
                                + Add New Address
                            </button>

                            <!-- New Address Form -->
                            <div x-show="showAddressForm" x-cloak class="mt-4 p-4 border border-gray-200 rounded">
                                <form @submit.prevent="saveAddress" class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                            <input type="text" x-model="newAddress.name" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                            <input type="tel" x-model="newAddress.phone" required pattern="[0-9]{10}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                                        <input type="text" x-model="newAddress.address_line_1" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2 (Optional)</label>
                                        <input type="text" x-model="newAddress.address_line_2"
                                               class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                            <input type="text" x-model="newAddress.city" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                            <input type="text" x-model="newAddress.state" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                                            <input type="text" x-model="newAddress.pincode" required pattern="[0-9]{6}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address Type</label>
                                        <select x-model="newAddress.type" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                            <option value="home">Home</option>
                                            <option value="office">Office</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" x-model="newAddress.is_default" id="is_default"
                                               class="text-blue-600 focus:ring-blue-500">
                                        <label for="is_default" class="text-sm text-gray-700">Set as default address</label>
                                    </div>
                                    <button type="submit" :disabled="savingAddress"
                                            class="w-full bg-xelnova-gold-500 hover:bg-xelnova-gold-600 text-white font-bold py-2 px-6 rounded shadow-sm text-sm uppercase disabled:opacity-70">
                                        <span x-show="!savingAddress">Save Address</span>
                                        <span x-show="savingAddress">Saving...</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Billing Address -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 bg-blue-50 flex items-center gap-3">
                            <span class="bg-blue-600 text-white font-bold px-2 py-0.5 text-xs rounded">3</span>
                            <h3 class="font-bold text-gray-800 uppercase text-sm">Billing Address</h3>
                        </div>
                        
                        <div class="p-4">
                            <!-- Same as Delivery Checkbox -->
                            <div class="flex items-center gap-2 mb-4">
                                <input type="checkbox" 
                                       x-model="sameAsDelivery" 
                                       id="same_as_delivery"
                                       class="text-blue-600 focus:ring-blue-500">
                                <label for="same_as_delivery" class="text-sm font-medium text-gray-700">
                                    Use same address as delivery
                                </label>
                            </div>

                            <!-- Billing Address Selection (shown when not same as delivery) -->
                            <div x-show="!sameAsDelivery && addresses.length > 0" x-cloak class="space-y-4 mb-4">
                                <template x-for="address in addresses" :key="address.id">
                                    <label class="flex items-start gap-3 p-4 border rounded cursor-pointer transition"
                                           :class="selectedBillingAddressId === address.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                        <input type="radio" 
                                               name="billing_address" 
                                               :value="address.id"
                                               x-model="selectedBillingAddressId"
                                               class="mt-1 text-blue-600 focus:ring-blue-500">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-bold text-gray-800" x-text="address.name"></span>
                                                <span class="bg-gray-200 text-gray-600 text-[10px] px-1.5 py-0.5 rounded uppercase font-bold" x-text="address.type"></span>
                                                <span class="font-bold text-gray-800 ml-2" x-text="address.phone"></span>
                                            </div>
                                            <p class="text-sm text-gray-600" x-text="address.full_address"></p>
                                        </div>
                                    </label>
                                </template>
                            </div>

                            <!-- Add New Billing Address (shown when not same as delivery) -->
                            <template x-if="!sameAsDelivery">
                                <button @click="showBillingAddressForm = !showBillingAddressForm" 
                                        class="w-full border-2 border-dashed border-gray-300 hover:border-blue-500 text-blue-600 font-bold py-3 rounded text-sm uppercase transition"
                                        x-text="showBillingAddressForm ? 'Cancel' : '+ Add New Billing Address'">
                                    + Add New Billing Address
                                </button>
                            </template>

                            <!-- New Billing Address Form -->
                            <div x-show="showBillingAddressForm && !sameAsDelivery" x-cloak class="mt-4 p-4 border border-gray-200 rounded">
                                <form @submit.prevent="saveBillingAddress" class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                            <input type="text" x-model="newBillingAddress.name" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                            <input type="tel" x-model="newBillingAddress.phone" required pattern="[0-9]{10}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                                        <input type="text" x-model="newBillingAddress.address_line_1" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2 (Optional)</label>
                                        <input type="text" x-model="newBillingAddress.address_line_2"
                                               class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                            <input type="text" x-model="newBillingAddress.city" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                            <input type="text" x-model="newBillingAddress.state" required
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                                            <input type="text" x-model="newBillingAddress.pincode" required pattern="[0-9]{6}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address Type</label>
                                        <select x-model="newBillingAddress.type" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                            <option value="home">Home</option>
                                            <option value="office">Office</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <button type="submit" :disabled="savingBillingAddress"
                                            class="w-full bg-xelnova-gold-500 hover:bg-xelnova-gold-600 text-white font-bold py-2 px-6 rounded shadow-sm text-sm uppercase disabled:opacity-70">
                                        <span x-show="!savingBillingAddress">Save Billing Address</span>
                                        <span x-show="savingBillingAddress">Saving...</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Payment -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-4 bg-blue-50 flex items-center gap-3">
                            <span class="bg-blue-600 text-white font-bold px-2 py-0.5 text-xs rounded">4</span>
                            <h3 class="font-bold text-gray-800 uppercase text-sm">Payment Options</h3>
                        </div>
                        
                        <div class="p-4">
                            <div class="flex items-center gap-3 p-4 border border-gray-200 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                                <div>
                                    <p class="font-bold text-gray-800">UPI / Cards / Wallets / NetBanking</p>
                                    <p class="text-sm text-gray-500">Pay securely via Razorpay</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="w-full lg:w-1/3">
                    <div class="bg-white rounded-lg shadow-sm p-4 sticky top-24">
                        <h2 class="text-gray-500 font-medium uppercase text-sm border-b pb-3 mb-4">Price Details</h2>
                        
                        <div class="space-y-4 mb-4 border-b pb-4">
                            <div class="flex justify-between text-gray-800">
                                <span>Price (<span x-text="cart.products_count"></span> items)</span>
                                <span x-text="'₹' + cart.subtotal.toLocaleString('en-IN')"></span>
                            </div>
                            <div class="flex justify-between text-gray-800" x-show="cart.discount > 0">
                                <span>Discount</span>
                                <span class="text-green-600" x-text="'- ₹' + cart.discount.toLocaleString('en-IN')"></span>
                            </div>
                            <div class="flex justify-between text-gray-800" x-show="cart.tax > 0">
                                <span>Tax (GST)</span>
                                <span x-text="'₹' + cart.tax.toLocaleString('en-IN')"></span>
                            </div>
                            <div class="flex justify-between text-gray-800">
                                <span>Delivery Charges</span>
                                <span :class="cart.shipping_charge > 0 ? '' : 'text-green-600'" 
                                      x-text="cart.shipping_charge > 0 ? '₹' + cart.shipping_charge.toLocaleString('en-IN') : 'Free'">
                                </span>
                            </div>
                        </div>

                        <div class="flex justify-between text-lg font-bold text-gray-900 mb-4">
                            <span>Total Amount</span>
                            <span x-text="'₹' + cart.total.toLocaleString('en-IN')"></span>
                        </div>

                        <p class="text-green-600 font-medium text-sm mb-4" x-show="cart.savings > 0">
                            You will save ₹<span x-text="cart.savings.toLocaleString('en-IN')"></span> on this order
                        </p>

                        <!-- Error Message -->
                        <div x-show="error" class="bg-red-50 text-red-600 p-3 rounded text-sm mb-4" x-text="error"></div>

                        <button @click="proceedToPayment" 
                                :disabled="!selectedAddressId || processing"
                                class="w-full bg-xelnova-green-600 hover:bg-xelnova-green-700 text-white font-bold py-3 px-6 rounded shadow-sm transition uppercase text-sm disabled:opacity-70 flex items-center justify-center gap-2">
                            <svg x-show="processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="processing ? 'Processing...' : 'Proceed to Payment'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Razorpay Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <script>
        function checkoutPage(initialAddresses, initialCart) {
            return {
                addresses: initialAddresses,
                cart: initialCart,
                selectedAddressId: initialAddresses.find(a => a.is_default)?.id || initialAddresses[0]?.id || null,
                showAddressForm: initialAddresses.length === 0,
                newAddress: {
                    name: '',
                    phone: '',
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state: '',
                    pincode: '',
                    type: 'home',
                    is_default: false
                },
                savingAddress: false,
                // Billing address state
                sameAsDelivery: true,
                selectedBillingAddressId: initialAddresses.find(a => a.is_default)?.id || initialAddresses[0]?.id || null,
                showBillingAddressForm: false,
                newBillingAddress: {
                    name: '',
                    phone: '',
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state: '',
                    pincode: '',
                    type: 'home',
                    is_default: false
                },
                savingBillingAddress: false,
                processing: false,
                error: '',

                async saveAddress() {
                    this.savingAddress = true;
                    this.error = '';

                    try {
                        const response = await fetch('/api/v1/addresses', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.newAddress)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.addresses.push(data.data);
                            this.selectedAddressId = data.data.id;
                            this.showAddressForm = false;
                            // Reset form
                            this.newAddress = {
                                name: '',
                                phone: '',
                                address_line_1: '',
                                address_line_2: '',
                                city: '',
                                state: '',
                                pincode: '',
                                type: 'home',
                                is_default: false
                            };
                        } else {
                            this.error = data.message || 'Failed to save address';
                        }
                    } catch (error) {
                        console.error('Error saving address:', error);
                        this.error = 'Failed to save address. Please try again.';
                    } finally {
                        this.savingAddress = false;
                    }
                },

                async saveBillingAddress() {
                    this.savingBillingAddress = true;
                    this.error = '';

                    try {
                        const response = await fetch('/api/v1/addresses', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.newBillingAddress)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.addresses.push(data.data);
                            this.selectedBillingAddressId = data.data.id;
                            this.showBillingAddressForm = false;
                            // Reset form
                            this.newBillingAddress = {
                                name: '',
                                phone: '',
                                address_line_1: '',
                                address_line_2: '',
                                city: '',
                                state: '',
                                pincode: '',
                                type: 'home',
                                is_default: false
                            };
                        } else {
                            this.error = data.message || 'Failed to save billing address';
                        }
                    } catch (error) {
                        console.error('Error saving billing address:', error);
                        this.error = 'Failed to save billing address. Please try again.';
                    } finally {
                        this.savingBillingAddress = false;
                    }
                },

                async proceedToPayment() {
                    if (!this.selectedAddressId) {
                        this.error = 'Please select a delivery address';
                        return;
                    }

                    // Check billing address
                    if (!this.sameAsDelivery && !this.selectedBillingAddressId) {
                        this.error = 'Please select a billing address';
                        return;
                    }

                    this.processing = true;
                    this.error = '';

                    try {
                        // Create Razorpay order
                        const response = await fetch('/checkout/create-razorpay-order', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                address_id: this.selectedAddressId,
                                billing_address_id: this.sameAsDelivery ? this.selectedAddressId : this.selectedBillingAddressId
                            })
                        });

                        const data = await response.json();

                        if (!data.success) {
                            this.error = data.message || 'Failed to create payment order';
                            this.processing = false;
                            return;
                        }

                        // Open Razorpay checkout
                        const options = {
                            key: data.data.key,
                            amount: data.data.amount,
                            currency: data.data.currency,
                            name: data.data.name,
                            description: data.data.description,
                            order_id: data.data.order_id,
                            prefill: data.data.prefill,
                            theme: {
                                color: '#10b981'
                            },
                            handler: async (response) => {
                                await this.verifyPayment(response);
                            },
                            modal: {
                                ondismiss: () => {
                                    this.processing = false;
                                }
                            }
                        };

                        const rzp = new Razorpay(options);
                        rzp.open();
                    } catch (error) {
                        console.error('Error creating payment order:', error);
                        this.error = 'Failed to initiate payment. Please try again.';
                        this.processing = false;
                    }
                },

                async verifyPayment(paymentResponse) {
                    try {
                        const response = await fetch('/checkout/verify-payment', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                razorpay_order_id: paymentResponse.razorpay_order_id,
                                razorpay_payment_id: paymentResponse.razorpay_payment_id,
                                razorpay_signature: paymentResponse.razorpay_signature,
                                address_id: this.selectedAddressId,
                                billing_address_id: this.sameAsDelivery ? this.selectedAddressId : this.selectedBillingAddressId
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Redirect to order confirmation
                            window.location.href = `/order-confirmation/${data.data.order_number}`;
                        } else {
                            this.error = data.message || 'Payment verification failed';
                            this.processing = false;
                        }
                    } catch (error) {
                        console.error('Error verifying payment:', error);
                        this.error = 'Payment verification failed. Please contact support.';
                        this.processing = false;
                    }
                }
            };
        }
    </script>
</x-marketplace.layout>
