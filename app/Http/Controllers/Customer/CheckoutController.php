<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected OrderService $orderService;

    public function __construct(CartService $cartService, OrderService $orderService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
    }

    /**
     * Show checkout page
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('customer.login')->with('redirect', route('marketplace.checkout'));
        }

        if (Auth::user()->isSeller()) {
            return redirect()->route('marketplace.cart')
                ->with('error', 'Seller accounts cannot place marketplace orders. Please login with a customer account.');
        }

        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            return redirect()->route('marketplace.cart')->with('error', 'Your cart is empty');
        }

        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->get();
        $cartSummary = $this->cartService->getCartSummary($cart);

        // Debug logging
        Log::info('Checkout Data', [
            'addresses_count' => $addresses->count(),
            'cart_summary' => $cartSummary,
        ]);

        return view('marketplace.checkout', compact('addresses', 'cartSummary'));
    }

    /**
     * Create Razorpay order
     */
    public function createRazorpayOrder(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to proceed',
            ], 401);
        }

        if (Auth::user()->isSeller()) {
            return response()->json([
                'success' => false,
                'message' => 'Seller accounts cannot place marketplace orders. Please login with a customer account.',
            ], 403);
        }

        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'billing_address_id' => 'required|exists:addresses,id',
        ]);

        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty',
            ], 400);
        }

        // Verify address belongs to user
        $address = Auth::user()->addresses()->findOrFail($request->address_id);

        try {
            // Calculate total in paise (Razorpay uses smallest currency unit)
            $cartSummary = $this->cartService->getCartSummary($cart);
            $total = $cartSummary['total'];
            $amountInPaise = (int) ($total * 100);

            // Initialize Razorpay API
            $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

            // Create Razorpay order
            $razorpayOrder = $api->order->create([
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => 'order_' . time(),
                'notes' => [
                    'user_id' => Auth::id(),
                    'address_id' => $address->id,
                ],
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $razorpayOrder->id,
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                    'key' => config('services.razorpay.key'),
                    'name' => config('app.name'),
                    'description' => 'Order Payment',
                    'prefill' => [
                        'name' => Auth::user()->name,
                        'email' => Auth::user()->email,
                        'contact' => Auth::user()->phone ?? $address->phone,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify payment and create order
     */
    public function verifyPayment(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to proceed',
            ], 401);
        }

        if (Auth::user()->isSeller()) {
            return response()->json([
                'success' => false,
                'message' => 'Seller accounts cannot place marketplace orders. Please login with a customer account.',
            ], 403);
        }

        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'address_id' => 'required|exists:addresses,id',
            'billing_address_id' => 'required|exists:addresses,id',
        ]);

        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty',
            ], 400);
        }

        try {
            // Verify Razorpay signature
            $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Get addresses
            $shippingAddress = Auth::user()->addresses()->findOrFail($request->address_id);
            $billingAddress = Auth::user()->addresses()->findOrFail($request->billing_address_id);

            // Calculate shipping
            $cartSummary = $this->cartService->getCartSummary($cart);
            $shippingCharge = $cartSummary['shipping_charge'];

            // Create order
            $order = $this->orderService->createOrderFromCart($cart, [
                'shipping_address' => $shippingAddress->toOrderSnapshot(),
                'billing_address' => $billingAddress->toOrderSnapshot(),
                'shipping_charge' => $shippingCharge,
                'payment_method' => 'razorpay',
            ]);

            // Process payment
            $processed = $this->orderService->processPayment($order, [
                'payment_id' => $request->razorpay_payment_id,
                'order_id' => $request->razorpay_order_id,
            ]);

            if (!$processed) {
                $this->orderService->handlePaymentFailure($order);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment captured but order finalization failed. Please contact support.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::error('Razorpay signature verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please contact support.',
            ], 500);
        }
    }

    /**
     * Show order confirmation page
     */
    public function confirmation($orderNumber)
    {
        if (!Auth::check()) {
            return redirect()->route('customer.login');
        }

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with(['items.product', 'items.seller'])
            ->firstOrFail();

        return view('marketplace.order-confirmation', compact('order'));
    }
}
