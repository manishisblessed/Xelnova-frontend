<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerLedgerEntry;
use App\Models\SellerPayoutRequest;
use App\Models\SubOrder;
use App\Services\Finance\LedgerService;
use App\Services\Finance\PayoutService;
use App\Services\Sms\SmsService;
use App\Services\Shipping\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SellerDashboardController extends Controller
{
    protected $shippingService;
    protected SmsService $smsService;
    protected LedgerService $ledgerService;
    protected PayoutService $payoutService;

    public function __construct(
        ShippingService $shippingService,
        SmsService $smsService,
        LedgerService $ledgerService,
        PayoutService $payoutService
    )
    {
        $this->shippingService = $shippingService;
        $this->smsService = $smsService;
        $this->ledgerService = $ledgerService;
        $this->payoutService = $payoutService;
    }

    public function dashboard()
    {
        $seller = auth()->user()->seller;
        $userId = auth()->id();

        // 1. Total Sales (only from completed/delivered suborders for this seller)
        // Adjust status check based on what you consider a "sale" (e.g., delivered or just confirmed)
        $totalSales = SubOrder::where('seller_id', $userId)
            ->whereIn('status', ['delivered', 'shipped', 'out_for_delivery', 'confirmed', 'packed'])
            ->sum('total');

        // 2. Total Orders Count
        $totalOrders = SubOrder::where('seller_id', $userId)->count();

        // 3. Active Products Count
        $activeProducts = \App\Models\Product::where('seller_id', $userId)
            ->where('is_active', true)
            ->count();

        // 4. Pending Orders (pending or processing)
        $pendingOrders = SubOrder::where('seller_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        // 5. Recent Orders (Latest 5)
        $recentOrders = SubOrder::where('seller_id', $userId)
            ->with(['order.user', 'items.product']) // Eager load for display
            ->latest()
            ->take(5)
            ->get();

        // 6. Monthly Sales Data for Chart (Last 6 months)
        $salesData = SubOrder::where('seller_id', $userId)
            ->whereIn('status', ['delivered', 'shipped', 'out_for_delivery', 'confirmed', 'packed'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill missing months with 0
        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $chartLabels[] = now()->subMonths($i)->format('M Y');
            $chartData[] = $salesData[$month] ?? 0;
        }

        // 7. Top Selling Products
        $topProducts = \App\Models\OrderItem::whereHas('subOrder', function($q) use ($userId) {
                $q->where('seller_id', $userId);
            })
            ->select('product_id', \DB::raw('SUM(quantity) as total_qty'), \DB::raw('SUM(price * quantity) as total_revenue'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('seller.dashboard', compact(
            'seller',
            'totalSales',
            'totalOrders',
            'activeProducts',
            'pendingOrders',
            'recentOrders',
            'chartLabels',
            'chartData',
            'topProducts'
        ));
    }

    public function products()
    {
        return view('seller.products.index');
    }

    public function createProduct()
    {
        return view('seller.products.form');
    }

    public function orders(Request $request)
    {
        $query = SubOrder::where('seller_id', auth()->id())
            ->with(['order.user', 'items']);

        // Filter by status
        if ($request->has('status') && $request->status != 'all' && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Search query
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('sub_order_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('order', function($oq) use ($searchTerm) {
                      $oq->where('order_number', 'like', "%{$searchTerm}%")
                         ->orWhereHas('user', function($uq) use ($searchTerm) {
                             $uq->where('name', 'like', "%{$searchTerm}%");
                         });
                  });
            });
        }

        $subOrders = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('seller.orders.index', compact('subOrders'));
    }

    public function orderDetail($id)
    {
        // Get sub-order for this seller only
        $subOrder = SubOrder::where('seller_id', auth()->id())
            ->with(['order.user', 'items.product'])
            ->findOrFail($id);

        return view('seller.orders.detail', compact('subOrder'));
    }

    public function invoice($id)
    {
        // Get sub-order for this seller only
        $subOrder = SubOrder::where('seller_id', auth()->id())
            ->with(['order.user', 'items.product', 'seller'])
            ->findOrFail($id);

        return view('seller.orders.invoice', compact('subOrder'));
    }

    public function updateStatus(Request $request, $id)
    {
        $subOrder = SubOrder::where('seller_id', auth()->id())
            ->with('order.user')
            ->findOrFail($id);

        $request->validate([
            'status' => 'required|in:confirmed,processing,packed,shipped,out_for_delivery,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:255',
            'courier' => 'nullable|string|max:255',
            'seller_notes' => 'nullable|string',
        ]);

        $previousStatus = $subOrder->status;

        $subOrder->updateStatus($request->status);

        if ($request->status === 'delivered') {
            $this->ledgerService->postDeliveredSale($subOrder->fresh());
        }

        if ($request->filled('tracking_number')) {
            $subOrder->update([
                'tracking_number' => $request->tracking_number,
                'courier' => $request->courier,
            ]);
        }

        if ($request->filled('seller_notes')) {
            $subOrder->update(['seller_notes' => $request->seller_notes]);
        }

        // Send email notification to customer for key status changes
        if (in_array($request->status, ['shipped', 'out_for_delivery', 'delivered', 'cancelled'])) {
            try {
                \Mail::to($subOrder->order->user->email)->send(
                    new \App\Mail\SubOrderStatusUpdated($subOrder, $previousStatus)
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send sub-order status email: ' . $e->getMessage());
            }
        }

        $this->sendLifecycleSms($subOrder, $request->status);

        return back()->with('success', 'Order status updated successfully! Customer has been notified via email.');
    }

    public function cancelOrder(Request $request, $id)
    {
        $subOrder = SubOrder::where('seller_id', auth()->id())->findOrFail($id);

        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        if (!$subOrder->canBeCancelled()) {
            return back()->with('error', 'This order cannot be cancelled at this stage.');
        }

        $success = $subOrder->cancel($request->cancel_reason);

        if ($success) {
            // Send cancellation email to customer
            try {
                \Mail::to($subOrder->order->user->email)->send(
                    new \App\Mail\SubOrderStatusUpdated($subOrder, 'processing')
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send cancellation email: ' . $e->getMessage());
            }

            return back()->with('success', 'Order cancelled successfully. Stock has been restored and customer has been notified.');
        }

        return back()->with('error', 'Failed to cancel order.');
    }

    public function finance()
    {
        $sellerUserId = auth()->id();
        $sellerProfile = auth()->user()->seller()->with(['bankAccounts'])->first();
        $verifiedBank = $sellerProfile?->bankAccounts->firstWhere('verification_status', 'verified');

        $summary = [
            'total_earned' => (float) SellerLedgerEntry::query()
                ->where('seller_id', $sellerUserId)
                ->where('entry_type', 'sale_credit')
                ->sum('amount'),
            'commission_paid' => (float) SellerLedgerEntry::query()
                ->where('seller_id', $sellerUserId)
                ->where('entry_type', 'commission_debit')
                ->sum('amount'),
            'requested' => (float) SellerPayoutRequest::query()
                ->where('seller_id', $sellerUserId)
                ->whereIn('status', ['pending', 'approved'])
                ->sum('requested_amount'),
            'paid_out' => (float) SellerPayoutRequest::query()
                ->where('seller_id', $sellerUserId)
                ->where('status', 'paid')
                ->sum('approved_amount'),
        ];
        $summary['available_balance'] = $this->ledgerService->calculateAvailableBalance($sellerUserId);

        $ledgerEntries = SellerLedgerEntry::query()
            ->where('seller_id', $sellerUserId)
            ->with(['subOrder.order', 'payoutRequest'])
            ->latest()
            ->paginate(15, ['*'], 'ledgerPage')
            ->withQueryString();

        $payoutRequests = SellerPayoutRequest::query()
            ->where('seller_id', $sellerUserId)
            ->with(['items.subOrder'])
            ->latest()
            ->paginate(10, ['*'], 'requestPage')
            ->withQueryString();

        $eligibility = [
            'has_verified_bank' => (bool) $verifiedBank,
            'min_amount' => 500.00,
            'has_pending_request' => SellerPayoutRequest::query()
                ->where('seller_id', $sellerUserId)
                ->where('status', 'pending')
                ->exists(),
            'commission_rate' => (float) ($sellerProfile?->commission_rate ?? 10.00),
        ];

        return view('seller.finance.index', compact(
            'summary',
            'ledgerEntries',
            'payoutRequests',
            'eligibility'
        ));
    }

    public function createPayoutRequest(Request $request)
    {
        $validated = $request->validate([
            'seller_notes' => 'nullable|string|max:500',
        ]);

        try {
            $payoutRequest = $this->payoutService->createRequestForFullBalance(
                auth()->id(),
                $validated['seller_notes'] ?? null
            );

            return redirect()->route('seller.finance')->with('success', 'Payout request ' . $payoutRequest->request_number . ' created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('seller.finance')->withErrors($e->errors())->withInput();
        }
    }

    public function financeLedger()
    {
        $ledgerEntries = SellerLedgerEntry::query()
            ->where('seller_id', auth()->id())
            ->with(['subOrder', 'payoutRequest'])
            ->latest()
            ->paginate(20);

        return response()->json($ledgerEntries);
    }

    protected function sendLifecycleSms(SubOrder $subOrder, string $status): void
    {
        $customerPhone = $subOrder->order->user->phone
            ?? ($subOrder->order->shipping_address['phone'] ?? null)
            ?? ($subOrder->order->billing_address['phone'] ?? null);

        if (!$customerPhone) {
            return;
        }

        try {
            if ($status === 'processing') {
                $this->smsService->sendOrderProcessing($customerPhone, $subOrder->sub_order_number);
                return;
            }

            if ($status === 'packed') {
                $this->smsService->sendOrderPacked($customerPhone, $subOrder->sub_order_number);
                return;
            }

            if ($status === 'shipped') {
                $this->smsService->sendOrderShipped(
                    $customerPhone,
                    $subOrder->sub_order_number,
                    $subOrder->courier ?: 'our logistics partner',
                    url('/account/track-order')
                );
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send lifecycle SMS', [
                'sub_order_id' => $subOrder->id,
                'sub_order_number' => $subOrder->sub_order_number,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function settings()
    {
        $seller = auth()->user()->seller;
        return view('seller.settings.index', compact('seller'));
    }

    public function updateSettings(Request $request, \App\Services\LocationService $locationService)
    {
        $seller = auth()->user()->seller;

        $request->validate([
            'postal_code' => 'required|string|max:10',
            // Add other setting validations here if needed
        ]);

        $postalCodeChanged = $seller->postal_code !== $request->postal_code;

        $seller->postal_code = $request->postal_code;
        
        // If postal code changed or lat/long missing, fetch coordinates
        if ($postalCodeChanged || !$seller->latitude || !$seller->longitude) {
            $coordinates = $locationService->getPincodeCoordinates($request->postal_code);
            
            if ($coordinates) {
                $seller->latitude = $coordinates['lat'];
                $seller->longitude = $coordinates['lng'];
            }
        }

        $seller->save();

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * AJAX: Check Shipping Rates
     */
    public function fetchShippingRates($id)
    {
        try {
            $subOrder = SubOrder::where('seller_id', auth()->id())
                ->with(['shipment', 'order', 'items.product', 'sellerProfile'])
                ->findOrFail($id);
            
            // Check if already shipped
            if ($subOrder->shipment) {
                return response()->json(['error' => 'Shipment already exists for this order'], 400);
            }
            
            $rates = $this->shippingService->getRates($subOrder);

            if (empty($rates)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No shipping rates are currently available from the configured providers. Please try again shortly.',
                ], 422);
            }
            
            return response()->json(['success' => true, 'rates' => $rates]);
        } catch (\Exception $e) {
            Log::error('Shipping Rate Error: ' . $e->getMessage());

            if (str_contains(strtolower($e->getMessage()), 'no shipping providers configured')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Shipping providers are not configured. Please set SHIPPING_PROVIDERS in .env.',
                ], 422);
            }

            return response()->json(['error' => 'Failed to fetch rates: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Action: Book Shipment
     */
    public function bookShipment(Request $request, $id)
    {
        try {
            $subOrder = SubOrder::where('seller_id', auth()->id())
                ->with(['order', 'items.product', 'sellerProfile'])
                ->findOrFail($id);
            
            $request->validate([
                'provider' => 'required|string',
                'service_code' => 'required|string',
            ]);
            
            $shipment = $this->shippingService->bookShipment(
                $subOrder, 
                $request->provider, 
                $request->service_code
            );
            
            // Update order status to shipped automatically? 
            // Or wait for user to click "Update Status"?
            // Usually booking generates label -> status 'shipped' (or 'ready_to_ship')
            
            return back()->with('success', 'Shipment booked successfully! AWB: ' . $shipment->awb_code);
        } catch (\Exception $e) {
            Log::error('Book Shipment Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to book shipment: ' . $e->getMessage());
        }
    }
}
