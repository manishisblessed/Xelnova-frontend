<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $subOrder->sub_order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .invoice-container { box-shadow: none; }
        }
        @page { size: A4; margin: 10mm; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Action Buttons (hidden in print) -->
    <div class="no-print fixed top-4 right-4 flex gap-3 z-50">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
            </svg>
            Print Invoice
        </button>
        <button onclick="downloadPDF()" class="bg-green-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-green-700 transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Download PDF
        </button>
        <a href="{{ route('seller.orders.detail', $subOrder->id) }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-700 transition">
            ← Back
        </a>
    </div>

    <!-- Invoice Container -->
    <div id="invoice" class="invoice-container max-w-4xl mx-auto my-8 bg-white shadow-lg">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold">XELNOVA</h1>
                    <p class="text-green-100 mt-1">Online Shopping Marketplace</p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-bold">TAX INVOICE</h2>
                    <p class="text-green-100 mt-1">Original for Recipient</p>
                </div>
            </div>
        </div>

        <!-- Invoice Details & QR Code -->
        <div class="p-8 border-b">
            <div class="flex justify-between">
                <div class="space-y-2">
                    <div class="flex gap-4">
                        <span class="text-gray-500 w-32">Invoice No:</span>
                        <span class="font-semibold">{{ $subOrder->sub_order_number }}</span>
                    </div>
                    <div class="flex gap-4">
                        <span class="text-gray-500 w-32">Invoice Date:</span>
                        <span class="font-semibold">{{ $subOrder->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex gap-4">
                        <span class="text-gray-500 w-32">Order Number:</span>
                        <span class="font-semibold">{{ $subOrder->order->order_number }}</span>
                    </div>
                    <div class="flex gap-4">
                        <span class="text-gray-500 w-32">Order Date:</span>
                        <span class="font-semibold">{{ $subOrder->order->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
                <!-- QR Code -->
                <div class="text-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($subOrder->sub_order_number) }}" alt="QR Code" class="w-28 h-28 mx-auto">
                    <p class="text-xs text-gray-500 mt-2">Scan for order details</p>
                </div>
            </div>
        </div>

        <!-- Seller & Customer Info -->
        <div class="p-8 grid grid-cols-2 gap-8 border-b">
            <!-- Seller Info -->
            <div>
                <h3 class="text-sm text-gray-500 uppercase font-semibold mb-3">Sold By</h3>
                <div class="text-sm">
                    <p class="font-bold text-gray-900">{{ $subOrder->seller->name ?? 'Seller' }}</p>
                    <p class="text-gray-600">{{ $subOrder->seller->email ?? '' }}</p>
                    @if($subOrder->seller->seller?->business_name)
                        <p class="text-gray-600">{{ $subOrder->seller->seller->business_name }}</p>
                    @endif
                    @if($subOrder->seller->seller?->gstin)
                        <p class="text-gray-600 mt-2">GSTIN: {{ $subOrder->seller->seller->gstin }}</p>
                    @endif
                </div>
            </div>

            <!-- Billing Info -->
            <div>
                <h3 class="text-sm text-gray-500 uppercase font-semibold mb-3">Bill To</h3>
                <div class="text-sm">
                    @php
                        $billing = $subOrder->order->billing_address ?? $subOrder->order->shipping_address;
                    @endphp
                    <p class="font-bold text-gray-900">{{ $billing['name'] ?? $subOrder->order->user->name }}</p>
                    <p class="text-gray-600">{{ $billing['address_line_1'] ?? '' }}</p>
                    @if(!empty($billing['address_line_2']))
                        <p class="text-gray-600">{{ $billing['address_line_2'] }}</p>
                    @endif
                    <p class="text-gray-600">{{ $billing['city'] ?? '' }}, {{ $billing['state'] ?? '' }} - {{ $billing['pincode'] ?? '' }}</p>
                    @if(!empty($billing['phone']))
                        <p class="text-gray-600 mt-2">Phone: {{ $billing['phone'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Shipping Address -->
        <div class="p-8 border-b bg-gray-50">
            <h3 class="text-sm text-gray-500 uppercase font-semibold mb-3">Ship To</h3>
            <div class="text-sm">
                @php $shipping = $subOrder->order->shipping_address; @endphp
                <p class="font-bold text-gray-900">{{ $shipping['name'] ?? '' }}</p>
                <p class="text-gray-600">
                    {{ $shipping['address_line_1'] ?? '' }}
                    @if(!empty($shipping['address_line_2'])), {{ $shipping['address_line_2'] }}@endif
                    @if(!empty($shipping['landmark'])), {{ $shipping['landmark'] }}@endif
                </p>
                <p class="text-gray-600">{{ $shipping['city'] ?? '' }}, {{ $shipping['state'] ?? '' }} - {{ $shipping['pincode'] ?? '' }}</p>
                @if(!empty($shipping['phone']))
                    <p class="text-gray-600">Phone: {{ $shipping['phone'] }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="p-8">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-left py-3 px-2 text-xs uppercase text-gray-600 font-semibold text-center border">Sl No</th>
                        <th class="text-left py-3 px-2 text-xs uppercase text-gray-600 font-semibold border w-1/4">Description</th>
                        <th class="text-right py-3 px-2 text-xs uppercase text-gray-600 font-semibold border">Unit Price</th>
                        <th class="text-right py-3 px-2 text-xs uppercase text-gray-600 font-semibold border">Discount</th>
                        <th class="text-center py-3 px-2 text-xs uppercase text-gray-600 font-semibold border">Qty</th>
                        <th class="text-right py-3 px-2 text-xs uppercase text-gray-600 font-semibold border">Net Amount</th>
                        <th class="text-center py-3 px-2 text-xs uppercase text-gray-600 font-semibold border">Tax Rate</th>
                        <th class="text-center py-3 px-2 text-xs uppercase text-gray-600 font-semibold border">Tax Type</th>
                        <th class="text-right py-3 px-2 text-xs uppercase text-gray-600 font-semibold border">Tax Amount</th>
                        <th class="text-right py-3 px-2 text-xs uppercase text-gray-600 font-semibold border">Shipping</th>
                        <th class="text-right py-3 px-2 text-xs uppercase text-gray-600 font-semibold border">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalTaxSum = 0; 
                        $totalNetSum = 0;
                    @endphp
                    @foreach($subOrder->items as $index => $item)
                        @php
                            // Calculate values for display
                            $taxRate = $item->tax_rate ?? 0;
                            $taxAmount = $item->tax_amount ?? 0;
                            $qty = $item->quantity;
                            $lineTotalDb = $item->total; // This is price * qty
                            
                            // Determine if inclusive or exclusive based on tax amount logic
                            // If Exclusive: tax = lineTotal * rate.
                            $expectedExclusiveTax = $lineTotalDb * ($taxRate / 100);
                             
                            $isExclusive = abs($taxAmount - $expectedExclusiveTax) < 0.1;
                            
                            if ($isExclusive) {
                                // Price was exclusive
                                $netAmount = $lineTotalDb;
                                $unitPrice = $netAmount / $qty;
                                $totalAmount = $netAmount + $taxAmount;
                            } else {
                                // Price was inclusive
                                $totalAmount = $lineTotalDb;
                                $netAmount = $totalAmount - $taxAmount;
                                $unitPrice = $netAmount / $qty;
                            }
                            
                            $totalTaxSum += $taxAmount;
                            $totalNetSum += $netAmount;
                        @endphp
                        <tr class="border-b">
                            <td class="py-2 px-2 text-center text-sm text-gray-600 border">{{ $index + 1 }}</td>
                            <td class="py-2 px-2 border">
                                <p class="font-medium text-gray-900 text-sm">{{ $item->product_name }}</p>
                                @if($item->variant_details && isset($item->variant_details['label']))
                                    <p class="text-xs text-gray-500">{{ $item->variant_details['label'] }}</p>
                                @elseif($item->product_options)
                                    <p class="text-xs text-gray-500">{{ implode(', ', $item->product_options) }}</p>
                                @endif
                            </td>
                            <td class="py-2 px-2 text-right text-sm text-gray-900 border">₹{{ number_format($unitPrice, 2) }}</td>
                            <td class="py-2 px-2 text-right text-sm text-gray-900 border">0.00</td>
                            <td class="py-2 px-2 text-center text-sm text-gray-900 border">{{ $qty }}</td>
                            <td class="py-2 px-2 text-right text-sm text-gray-900 border">₹{{ number_format($netAmount, 2) }}</td>
                            <td class="py-2 px-2 text-center text-sm text-gray-900 border">{{ $taxRate }}%</td>
                            <td class="py-2 px-2 text-center text-sm text-gray-900 border">GST</td>
                            <td class="py-2 px-2 text-right text-sm text-gray-900 border">₹{{ number_format($taxAmount, 2) }}</td>
                            <td class="py-2 px-2 text-right text-sm text-gray-900 border">
                                ₹{{ number_format($item->shipping_cost, 2) }}
                                @if($item->is_free_shipping)
                                    <br><span class="text-[10px] text-green-600 font-bold">(Free)</span>
                                @endif
                            </td>
                            <td class="py-2 px-2 text-right font-medium text-sm text-gray-900 border">₹{{ number_format($totalAmount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals -->
            <div class="mt-6 flex justify-end">
                <div class="w-72">
                    <div class="flex justify-between py-2 text-sm">
                        <span class="text-gray-600">Subtotal (Inclusive of GST)</span>
                        <span class="font-medium">₹{{ number_format($subOrder->total - $subOrder->shipping_charge, 2) }}</span>
                    </div>
                    @php
                        $grossShipping = $subOrder->items->sum('shipping_cost');
                        $shippingDiscount = $subOrder->items->where('is_free_shipping', true)->sum('shipping_cost');
                    @endphp

                    <div class="flex justify-between py-2 text-sm">
                        <span class="text-gray-600">Shipping Charges</span>
                        <span class="font-medium">₹{{ number_format($grossShipping, 2) }}</span>
                    </div>

                    @if($shippingDiscount > 0)
                        <div class="flex justify-between py-2 text-sm text-green-600">
                            <span>Shipping Discount</span>
                            <span>-₹{{ number_format($shippingDiscount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between py-2 text-sm">
                        <span class="text-gray-600">Total Tax</span>
                        <span class="font-medium">₹{{ number_format($totalTaxSum, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-3 text-lg font-bold border-t-2 border-gray-900 mt-2">
                        <span>Grand Total</span>
                        <span class="text-green-600">₹{{ number_format($subOrder->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount in Words -->
        <div class="px-8 pb-4 text-sm">
            <p class="text-gray-600">
                <span class="font-medium">Amount in Words:</span> 
                Rupees {{ ucwords(\NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format(floor($subOrder->total))) }} Only
            </p>
        </div>

        <!-- Footer -->
        <div class="p-8 bg-gray-50 border-t">
            <div class="grid grid-cols-2 gap-8">
                <div class="text-sm text-gray-600">
                    <h4 class="font-semibold text-gray-900 mb-2">Terms & Conditions</h4>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Goods once sold will not be taken back</li>
                        <li>Subject to seller's jurisdiction</li>
                        <li>E&OE - Errors and Omissions Excepted</li>
                    </ul>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 mb-12">For {{ $subOrder->seller->name ?? 'Seller' }}</p>
                    <p class="text-sm font-semibold text-gray-900 border-t border-gray-300 pt-2 inline-block">Authorized Signatory</p>
                </div>
            </div>
        </div>

        <!-- Computer Generated Note -->
        <div class="text-center py-4 text-xs text-gray-500 border-t">
            This is a computer-generated invoice and does not require a physical signature.
        </div>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('invoice');
            const opt = {
                margin: 10,
                filename: 'Invoice-{{ $subOrder->sub_order_number }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
