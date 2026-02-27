<x-seller.layout>
    @section('title', 'Order Detail - ' . $subOrder->sub_order_number)

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-start">
            <div>
                <a href="{{ route('seller.orders') }}" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
                    ← Back to Orders
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Order #{{ $subOrder->sub_order_number }}</h1>
                <p class="text-sm text-gray-600">Placed on {{ $subOrder->created_at->format('M d, Y \a\t h:i A') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('seller.orders.invoice', $subOrder->id) }}" target="_blank" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    View Invoice
                </a>
                <span class="{{ $subOrder->status_badge_class }} px-4 py-2 rounded text-sm font-medium uppercase">
                    {{ str_replace('_', ' ', $subOrder->status) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Items -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Order Items</h2>
                    <div class="space-y-4">
                        @foreach($subOrder->items as $item)
                            <div class="flex gap-4 pb-4 border-b last:border-0">
                                @if($item->product_image)
                                    @php
                                        $imageUrl = filter_var($item->product_image, FILTER_VALIDATE_URL) 
                                            ? $item->product_image 
                                            : asset('storage/' . $item->product_image);
                                    @endphp
                                    <img src="{{ $imageUrl }}" class="w-20 h-20 rounded object-cover">
                                @else
                                    <div class="w-20 h-20 bg-gray-200 rounded"></div>
                                @endif
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">{{ $item->product_name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">Quantity: {{ $item->quantity }}</p>
                                    @if($item->variant_details && isset($item->variant_details['label']))
                                        <p class="text-xs text-gray-500 mt-1">{{ $item->variant_details['label'] }}</p>
                                    @elseif($item->product_options)
                                        <p class="text-xs text-gray-500">{{ implode(', ', $item->product_options) }}</p>
                                    @endif

                                    <div class="mt-2">
                                        <span class="inline-block text-[10px] uppercase font-bold {{ $item->is_inclusive_tax ? 'text-gray-500' : 'text-orange-600' }}">
                                            {{ $item->is_inclusive_tax ? '(Tax Inclusive)' : '+ ' . number_format($item->tax_rate, 0) . '% Tax' }}
                                        </span>
                                        <br>
                                        <span class="inline-block mt-1 text-[10px] uppercase font-bold {{ $item->is_free_shipping ? 'text-green-600' : 'text-gray-500' }}">
                                            {{ $item->is_free_shipping ? 'Free Shipping' : '+ ₹' . number_format($item->shipping_cost, 2) . ' Shipping' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900">₹{{ number_format($item->total, 2) }}</p>
                                    <p class="text-sm text-gray-500">₹{{ number_format($item->price, 2) }} each</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Totals -->
                    <div class="mt-6 pt-4 border-t space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal (Inclusive of GST)</span>
                            <span class="font-medium">₹{{ number_format($subOrder->total - $subOrder->shipping_charge, 2) }}</span>
                        </div>
                        
                        @php
                            // Calculate Gross Shipping and Discount for display
                            $grossShipping = $subOrder->items->sum('shipping_cost');
                            $shippingDiscount = $subOrder->items->where('is_free_shipping', true)->sum('shipping_cost');
                            $netShipping = $subOrder->shipping_charge;
                            
                            // Iterate items to calculate Tax Info for display (since subOrder->tax might be just extra tax)
                            // We want to show "Includes GST: ₹XXX" info if it's inclusive, or "Tax (GST): ₹XXX" if added.
                            // The user requested: "Tax (GST) ₹(tax amount as per set in Price)"
                            // And "Total" remaining same.
                            
                            $totalTaxAmount = $subOrder->items->sum('tax_amount');
                        @endphp
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium">₹{{ number_format($grossShipping, 2) }}</span>
                        </div>
                        
                        @if($shippingDiscount > 0)
                            <div class="flex justify-between text-sm text-green-600">
                                <span>Shipping Discount</span>
                                <span>-₹{{ number_format($shippingDiscount, 2) }}</span>
                            </div>
                        @elseif($grossShipping > 0 && $netShipping == 0)
                             <!-- Fallback in case is_free_shipping wasn't set but net is 0? Unlikely with new logic -->
                        @endif

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax (GST)</span>
                            <span class="font-medium">₹{{ number_format($totalTaxAmount, 2) }}</span>
                        </div>

                        <div class="flex justify-between text-base font-bold pt-2 border-t">
                            <span>Total</span>
                            <span class="text-xelnova-green-600">₹{{ number_format($subOrder->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Shipment Management -->
                <div class="bg-white rounded-lg shadow-sm p-6" x-data="shippingHandler()">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Shipping & Logistics</h2>
                    
                    @if($subOrder->shipment)
                        <!-- Existing Shipment Details -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm text-blue-900 font-bold">Shipment Booked</p>
                                    <p class="text-sm text-blue-800 mt-1">Provider: <span class="font-medium">{{ ucfirst($subOrder->shipment->provider) }}</span></p>
                                    <p class="text-sm text-blue-800">AWB Code: <span class="font-mono font-medium">{{ $subOrder->shipment->awb_code }}</span></p>
                                    <p class="text-sm text-blue-800">Service: {{ $subOrder->shipment->service_type }}</p>
                                </div>
                                <div class="text-right">
                                    <a href="{{ $subOrder->shipment->label_url }}" target="_blank" class="inline-flex items-center gap-1 bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd" />
                                        </svg>
                                        {{ strtolower($subOrder->shipment->provider) === 'ekart' ? 'Track Shipment' : 'Download Label' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Rate Checker -->
                        <div x-show="!rates.length && !loading">
                            <p class="text-sm text-gray-600 mb-4">Fetch real-time shipping rates from available providers.</p>
                            <button @click="fetchRates" type="button" class="w-full sm:w-auto bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.126-.504 1.126-1.125V15.75M16.5 7.5V12.75a2.25 2.25 0 01-2.25 2.25H2.51l-4.05-1.8A.75.75 0 002.51 13h15.75a2.25 2.25 0 012.25 2.25v2.25M16.5 7.5V12.75a2.25 2.25 0 01-2.25 2.25H13.5m-3-6v2.25" />
                                </svg>
                                Check Shipping Rates
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div x-show="loading" class="text-center py-4">
                            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-gray-500 mt-2">Fetching best rates for you...</p>
                        </div>

                        <!-- Rates Table -->
                        <div x-show="rates.length > 0" class="mt-4">
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Delivery</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="rate in rates" :key="rate.provider + rate.service_type">
                                            <tr>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900" x-text="rate.provider"></td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500" x-text="rate.service_type"></td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-bold" x-text="'₹' + rate.price"></td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500" x-text="rate.estimated_date"></td>
                                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                                    <form action="{{ route('seller.orders.book-shipping', $subOrder->id) }}" method="POST" @submit="loading = true">
                                                        @csrf
                                                        <input type="hidden" name="provider" :value="rate.provider">
                                                        <input type="hidden" name="service_code" :value="rate.meta_data.service_code">
                                                        <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-xs font-medium hover:bg-indigo-700 transition">
                                                            Book Now
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <button @click="rates = []" class="mt-3 text-sm text-gray-500 hover:text-gray-700 underline">Cancel / Clear</button>
                        </div>
                    @endif

                    <script>
                        function shippingHandler() {
                            return {
                                loading: false,
                                rates: [],
                                fetchRates() {
                                    this.loading = true;
                                    fetch('{{ route("seller.orders.shipping-rates", $subOrder->id) }}')
                                        .then(response => response.json())
                                        .then(data => {
                                            this.loading = false;
                                            if (data.success) {
                                                this.rates = data.rates;
                                            } else {
                                                alert(data.error || 'Failed to fetch rates');
                                            }
                                        })
                                        .catch(err => {
                                            this.loading = false;
                                            console.error(err);
                                            alert('Something went wrong. Please try again.');
                                        });
                                }
                            }
                        }
                    </script>
                </div>

                <!-- Update Status Form -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Update Order Status</h2>
                    
                    @if(session('success'))
                        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('seller.orders.update-status', $subOrder->id) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
                            <select name="status" class="w-full border-gray-300 rounded-lg focus:ring-xelnova-green-500 focus:border-xelnova-green-500">
                                <option value="confirmed" {{ $subOrder->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="processing" {{ $subOrder->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="packed" {{ $subOrder->status == 'packed' ? 'selected' : '' }}>Packed</option>
                                <option value="shipped" {{ $subOrder->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="out_for_delivery" {{ $subOrder->status == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                <option value="delivered" {{ $subOrder->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $subOrder->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tracking Number (optional)</label>
                            <input type="text" name="tracking_number" value="{{ $subOrder->tracking_number }}" 
                                   class="w-full border-gray-300 rounded-lg focus:ring-xelnova-green-500 focus:border-xelnova-green-500"
                                   placeholder="Enter tracking number">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Courier Service (optional)</label>
                            <input type="text" name="courier" value="{{ $subOrder->courier }}" 
                                   class="w-full border-gray-300 rounded-lg focus:ring-xelnova-green-500 focus:border-xelnova-green-500"
                                   placeholder="e.g., BlueDart, DTDC, Delhivery">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optional)</label>
                            <textarea name="seller_notes" rows="3" 
                                      class="w-full border-gray-300 rounded-lg focus:ring-xelnova-green-500 focus:border-xelnova-green-500"
                                      placeholder="Any notes for this order">{{ $subOrder->seller_notes }}</textarea>
                        </div>

                        <button type="submit" class="w-full bg-xelnova-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-xelnova-green-700 transition">
                            Update Status
                        </button>
                    </form>
                </div>

                <!-- Cancel Order (if eligible) -->
                @if($subOrder->canBeCancelled())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <h2 class="text-lg font-bold text-red-900 mb-4">Cancel Order</h2>
                        <p class="text-sm text-red-700 mb-4">
                            Cancelling this order will restore the stock and notify the customer via email.
                        </p>
                        
                        <form action="{{ route('seller.orders.cancel', $subOrder->id) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-red-900 mb-2">Cancellation Reason *</label>
                                <textarea name="cancel_reason" rows="3" required
                                          class="w-full border-red-300 rounded-lg focus:ring-red-500 focus:border-red-500"
                                          placeholder="Please provide a reason for cancelling this order"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-red-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-700 transition">
                                Cancel This Order
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Customer Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Customer Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Name</p>
                            <p class="font-medium text-gray-900">{{ $subOrder->order->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Email</p>
                            <p class="text-sm text-gray-700">{{ $subOrder->order->user->email }}</p>
                        </div>
                        @if($subOrder->order->user->phone)
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Phone</p>
                                <p class="text-sm text-gray-700">{{ $subOrder->order->user->phone }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Shipping Address</h3>
                    @if($subOrder->order->shipping_address)
                        <div class="text-sm text-gray-700">
                            <p class="font-medium">{{ $subOrder->order->shipping_address['name'] ?? '' }}</p>
                            <p>{{ $subOrder->order->shipping_address['address_line_1'] ?? '' }}</p>
                            @if(!empty($subOrder->order->shipping_address['address_line_2']))
                                <p>{{ $subOrder->order->shipping_address['address_line_2'] }}</p>
                            @endif
                            @if(!empty($subOrder->order->shipping_address['landmark']))
                                <p>{{ $subOrder->order->shipping_address['landmark'] }}</p>
                            @endif
                            <p>{{ $subOrder->order->shipping_address['city'] ?? '' }}, {{ $subOrder->order->shipping_address['state'] ?? '' }} - {{ $subOrder->order->shipping_address['pincode'] ?? '' }}</p>
                            @if(!empty($subOrder->order->shipping_address['phone']))
                                <p class="mt-2">Phone: {{ $subOrder->order->shipping_address['phone'] }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Billing Address -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Billing Address</h3>
                    @if($subOrder->order->billing_address)
                        <div class="text-sm text-gray-700">
                            <p class="font-medium">{{ $subOrder->order->billing_address['name'] ?? '' }}</p>
                            <p>{{ $subOrder->order->billing_address['address_line_1'] ?? '' }}</p>
                            @if(!empty($subOrder->order->billing_address['address_line_2']))
                                <p>{{ $subOrder->order->billing_address['address_line_2'] }}</p>
                            @endif
                            @if(!empty($subOrder->order->billing_address['landmark']))
                                <p>{{ $subOrder->order->billing_address['landmark'] }}</p>
                            @endif
                            <p>{{ $subOrder->order->billing_address['city'] ?? '' }}, {{ $subOrder->order->billing_address['state'] ?? '' }} - {{ $subOrder->order->billing_address['pincode'] ?? '' }}</p>
                            @if(!empty($subOrder->order->billing_address['phone']))
                                <p class="mt-2">Phone: {{ $subOrder->order->billing_address['phone'] }}</p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic">Same as shipping address</p>
                    @endif
                </div>

                <!-- Order Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Order Information</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Parent Order</p>
                            <p class="font-medium text-gray-900">{{ $subOrder->order->order_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Payment Status</p>
                            <span class="{{ $subOrder->order->payment_badge_class }} px-2 py-1 text-xs rounded uppercase">
                                {{ $subOrder->order->payment_status }}
                            </span>
                        </div>
                        @if($subOrder->confirmed_at)
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Confirmed At</p>
                                <p class="text-gray-700">{{ $subOrder->confirmed_at->format('M d, Y h:i A') }}</p>
                            </div>
                        @endif
                        @if($subOrder->shipped_at)
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Shipped At</p>
                                <p class="text-gray-700">{{ $subOrder->shipped_at->format('M d, Y h:i A') }}</p>
                            </div>
                        @endif
                        @if($subOrder->delivered_at)
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Delivered At</p>
                                <p class="text-gray-700">{{ $subOrder->delivered_at->format('M d, Y h:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            /* Hide non-essential elements */
            .no-print,
            nav,
            aside,
            button:not(.print-only),
            form,
            .bg-xelnova-green-600,
            .border-gray-300 {
                display: none !important;
            }

            /* Reset page styles */
            body {
                background: white !important;
                padding: 20px;
            }

            /* Invoice header */
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #000;
                padding-bottom: 20px;
            }

            /* Make content full width */
            .lg\:col-span-2 {
                grid-column: span 3 !important;
            }

            /* Adjust spacing */
            .space-y-6 > * {
                margin-bottom: 15px !important;
            }

            /* Print-friendly colors */
            .bg-white {
                background: white !important;
            }

            .shadow-sm {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            /* Ensure text is black */
            * {
                color: #000 !important;
            }

            /* Page breaks */
            .page-break {
                page-break-after: always;
            }
        }

        .print-only {
            display: none;
        }

        @media print {
            .print-only {
                display: block !important;
            }
        }
    </style>
    @endpush
</x-seller.layout>
