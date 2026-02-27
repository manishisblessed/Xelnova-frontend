<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Estimate delivery date based on pincode.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function estimate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'pincode' => 'required|string|max:10',
        ]);

        $product = Product::with('sellerProfile')->find($request->product_id);
        
        if (!$product || !$product->sellerProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Product or seller information unavailable'
            ], 404);
        }

        $seller = $product->sellerProfile;

        // Check if seller has location configured
        if (!$seller->latitude || !$seller->longitude) {
            // Fallback to default if seller hasn't set location
            // e.g. return standard 5-7 days
            return response()->json([
                'success' => true,
                'data' => [
                    'delivery_date' => now()->addDays(7)->format('D, M d'),
                    'message' => 'Standard Delivery'
                ]
            ]);
        }

        // Get customer coordinates
        $customerCoords = $this->locationService->getPincodeCoordinates($request->pincode);

        if (!$customerCoords) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Pincode'
            ], 400);
        }

        // Calculate distance
        $distance = $this->locationService->calculateDistance(
            $seller->latitude,
            $seller->longitude,
            $customerCoords['lat'],
            $customerCoords['lng']
        );

        Log::info("Delivery Estimate Debug:", [
            'seller_pincode' => $seller->postal_code, 
            'seller_lat' => $seller->latitude,
            'seller_lng' => $seller->longitude,
            'customer_pincode' => $request->pincode,
            'customer_lat' => $customerCoords['lat'],
            'customer_lng' => $customerCoords['lng'],
            'distance_km' => $distance
        ]);

        // Get delivery estimate
        $estimate = $this->locationService->getDeliveryEstimate($distance);

        return response()->json([
            'success' => true,
            'data' => [
                'delivery_date' => $estimate,
                'message' => 'Free delivery by ' . $estimate,
                'distance_km' => round($distance, 2)
            ]
        ]);
    }
}
