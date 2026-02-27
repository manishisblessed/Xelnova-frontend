<?php

namespace App\Services\Shipping\Providers;

use App\Models\Seller;
use App\Models\SubOrder;
use App\Services\Shipping\Contracts\ShippingProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DelhiveryProvider implements ShippingProvider
{
    protected $apiKey;
    protected $baseUrl;
    protected $isSandbox;

    public function __construct()
    {
        $this->apiKey = config('services.delhivery.key');
        $this->isSandbox = config('services.delhivery.sandbox');
        
        // Use production URL if key starts with 'prod' or sandbox is false (adjust based on real Delhivery config)
        // Delhivery usually has totally different domains.
        $this->baseUrl = $this->isSandbox 
            ? 'https://staging-express.delhivery.com' 
            : 'https://track.delhivery.com'; 
    }


    public function calculateRates(SubOrder $order, Seller $seller): array
    {
        // 1. Calculate Weight, Dimensions & Package Type
        $weight = 0;
        $maxLength = 0;
        $maxWidth = 0;
        $maxHeight = 0;
        $hasBox = false;

        foreach ($order->items as $item) {
             $product = $item->product;
             if ($product) {
                 $weight += ($product->weight ?? 500) * $item->quantity;
                 
                 // Heuristic: Package size is at least as big as the largest item
                 // (This is a simplification, real packing logic is complex)
                 $maxLength = max($maxLength, $product->length ?? 10);
                 $maxWidth = max($maxWidth, $product->width ?? 10);
                 $maxHeight = max($maxHeight, $product->height ?? 10);
                 
                 if (($product->packaging_type ?? 'box') === 'box') {
                     $hasBox = true;
                 }
             }
        }
        $weightGrams = $weight > 0 ? $weight : 500;
        $packageType = $hasBox ? 'box' : 'flyer';
        
        // 2. Prepare Pins
        $pickupPincode = $seller->postal_code;
        $destPincode = $order->order->shipping_address['pincode'] ?? '';

        Log::info("Delhivery Rates Check: Origin: $pickupPincode, Dest: $destPincode, Weight: $weightGrams, L: $maxLength, B: $maxWidth, H: $maxHeight, Type: $packageType");

        if (!$pickupPincode || !$destPincode) {
            Log::warning("Delhivery Rates: Missing Pincode");
            return [];
        }

        // Check Serviceability First (to debug connection and validity)
        try {
            $serviceabilityUrl = "https://track.delhivery.com/c/api/pin-codes/json/";
            
            // Staging URL for Serviceability might differ, but 'track.delhivery.com' is usually production.
            if ($this->isSandbox) {
                 $serviceabilityUrl = "https://staging-express.delhivery.com/c/api/pin-codes/json/";
            }

            $servResponse = Http::withHeaders([
                'Authorization' => 'Token ' . $this->apiKey
            ])->get($serviceabilityUrl, [
                'filter_codes' => $destPincode
            ]);
            
            Log::info("Delhivery Serviceability Check: " . $servResponse->status() . " Body: " . $servResponse->body());
            
        } catch (\Exception $e) {
             Log::error("Delhivery Serviceability Except: " . $e->getMessage());
        }

        $rates = [];
        $modes = ['S' => 'Surface', 'E' => 'Express'];
        
        // 3. Determine Payment Mode (from Parent Order)
        // Order payment_method usually 'cod' or 'prepaid' (or razorpay etc which is prepaid)
        $paymentType = $order->order->payment_method === 'cod' ? 'COD' : 'Pre-paid';

        foreach ($modes as $mode => $modeName) {
            try {
                // Correct Endpoint for Rate Calculation: Invoice API
                // Doc: https://track.delhivery.com/api/kinko/v1/invoice/charges/.json
                $url = "https://track.delhivery.com/api/kinko/v1/invoice/charges/.json";
                
                if ($this->isSandbox) {
                    $url = "https://staging-express.delhivery.com/api/kinko/v1/invoice/charges/.json";
                }

                Log::info("Delhivery Request Url ($mode): $url");
                $queryParams = [
                    'md' => $mode,
                    'ss' => 'Delivered',
                    'd_pin' => $destPincode,
                    'o_pin' => $pickupPincode,
                    'cgm' => $weightGrams,
                    'pt' => $paymentType,
                    'l' => $maxLength,
                    'b' => $maxWidth,
                    'h' => $maxHeight,
                    'ipkg_type' => $packageType
                ];

                Log::info("Delhivery Request Request Params ($mode): " . json_encode($queryParams));

                $response = Http::withHeaders([
                    'Authorization' => 'Token ' . $this->apiKey,
                    'Accept' => 'application/json'
                ])->get($url, $queryParams);

                Log::info("Delhivery Rate Response ($mode): " . $response->status() . " Body: " . $response->body());

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Response: [ { "total_amount": 150.00, ... } ] (Array of objects or Single Object)
                    // Usually it returns an array of charges.
                    
                    $chargeObj = null;
                    if (isset($data[0]) && is_array($data[0])) {
                        $chargeObj = $data[0];
                    } elseif (isset($data['total_amount'])) {
                        $chargeObj = $data;
                    }

                    $price = $chargeObj['total_amount'] ?? 0;

                    if ($price > 0) {
                        $rates[] = [
                            'provider' => 'delhivery',
                            'service_type' => $modeName,
                            'price' => $price,
                            'estimated_date' => now()->addDays($mode === 'E' ? 2 : 5)->format('Y-m-d'),
                            'meta_data' => ['service_code' => $mode, 'raw' => $chargeObj]
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Delhivery Rate Check Failed ($mode): " . $e->getMessage());
            }
        }

        return $rates;
    }

    public function createShipment(SubOrder $order, Seller $seller, string $serviceCode): ?array
    {
        // Prepare Shipment Data
        $shippingAddress = $order->order->shipping_address;
        $paymentMethod = $order->order->payment_method === 'cod' ? 'COD' : 'Prepaid';
        
        $shipmentData = [
            "name" => $shippingAddress['name'],
            "add" => $shippingAddress['address_line_1'] . ' ' . ($shippingAddress['address_line_2'] ?? ''),
            "pin" => $shippingAddress['pincode'],
            "city" => $shippingAddress['city'],
            "state" => $shippingAddress['state'],
            "country" => "India",
            "phone" => $shippingAddress['phone'],
            "order" => $order->sub_order_number,
            "payment_mode" => $paymentMethod,
            "products_desc" => "Order " . $order->sub_order_number, 
            "hsn_code" => "", // Optional but good 
            "cod_amount" => $paymentMethod === 'COD' ? $order->total : 0,
            "order_date" => $order->created_at->format('Y-m-d H:i:s'),
            "total_amount" => $order->total,
            "seller_add" => $seller->business_address,
            "seller_name" => $seller->business_name,
            "seller_inv" => "",
            "quantity" => $order->items_count,
            "waybill" => "", // Leave empty for auto-generation usually, or fetch waybill first if required
            "shipment_width" => 10, // Mock dimensions if not in product
            "shipment_height" => 10,
            "shipment_depth" => 10,
            "weight" => 500 // Should calculate real total weight
        ];

        // Pickup Location needs to be registered in Delhivery?
        // Usually 'pickup_location' object describes the warehouse.
        $pickupLocation = [
            "name" => $seller->business_name,
            "add" => $seller->business_address,
            "city" => $seller->city,
            "pin_code" => $seller->postal_code,
            "country" => "India",
            "phone" => $seller->phone // Ensure seller has phone
        ];

        // Delhivery Payload Format
        $payload = [
            "shipments" => [$shipmentData],
            "pickup_location" => $pickupLocation
        ];
        
        $formatData = 'format=json&data=' . json_encode($payload);

        try {
            // Note: Use 'asForm' because we are sending `data=JSON_STRING`
            $response = Http::asForm()
                ->withHeaders(['Authorization' => 'Token ' . $this->apiKey])
                ->post($this->baseUrl . "/api/cmu/create.json", [
                    'format' => 'json',
                    'data' => json_encode($payload)
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $package = $data['packages'][0] ?? null;
                
                // Check specifically for ClientWarehouse Error
                // Response might be: { "packages": [...], "success": false, "rmk": "ClientWarehouse matching query does not exist." }
                $remarks = $package['remarks'] ?? $data['rmk'] ?? '';
                
                // Fix: 'rmk' or 'remarks' can be an array in some error cases
                if (is_array($remarks)) {
                    $remarks = json_encode($remarks);
                }

                if (str_contains(strtolower((string)$remarks), 'clientwarehouse')) {
                     Log::info("Delhivery: Warehouse not found. Attempting to create warehouse: " . $seller->business_name);
                     
                     // Attempt to create warehouse
                     if ($this->createWarehouse($seller)) {
                         // Retry booking
                         Log::info("Delhivery: Warehouse created. Retrying booking...");
                         return $this->createShipment($order, $seller, $serviceCode);
                     } else {
                         throw new \Exception("Failed to auto-create warehouse for seller.");
                     }
                }
                
                if ($package && ($package['status'] ?? '') === 'Success') {
                     $awb = $package['waybill'];
                     return [
                        'provider' => 'delhivery',
                        'awb_code' => $awb,
                        'provider_order_id' => $package['refnum'] ?? $order->sub_order_number,
                        'service_type' => $serviceCode === 'S' ? 'Surface' : 'Express',
                        'courier_name' => 'Delhivery',
                        'label_url' => $this->baseUrl . "/api/p/packing_slip?wbns=$awb", 
                        'status' => 'pending',
                        'meta_data' => $data,
                        'tracking_history' => []
                    ];
                } else {
                    $error = $package['remarks'] ?? $data['rmk'] ?? 'Unknown Error';
                    
                    if (is_array($error)) {
                        $error = implode(', ', $error);
                    }
                    
                    Log::error("Delhivery Creation Error: " . json_encode($data));
                    throw new \Exception("Delhivery Error: " . $error);
                }
            } else {
               Log::error("Delhivery API Failed: " . $response->body()); 
               throw new \Exception("Delhivery API connection failed");
            }

        } catch (\Exception $e) {
            Log::error("Delhivery Shipment Exception: " . $e->getMessage());
            throw $e; 
        }
    }

    protected function createWarehouse(Seller $seller): bool
    {
        try {
            $url = $this->baseUrl . "/api/backend/clientwarehouse/create/";
            
            // Warehouse Creation Payload
            $payload = [
                "name" => $seller->business_name,
                "email" => $seller->user->email ?? 'noreply@xelnova.com',
                "phone" => $seller->phone,
                "address" => $seller->business_address,
                "city" => $seller->city,
                "country" => "India",
                "pin" => $seller->postal_code,
                "return_address" => $seller->business_address,
                "return_pin" => $seller->postal_code,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($url, $payload);

            Log::info("Delhivery Warehouse Create Response: " . $response->body());

            return $response->successful(); // Check explicitly for success field if needed

        } catch (\Exception $e) {
            Log::error("Delhivery Warehouse Create Failed: " . $e->getMessage());
            return false;
        }
    }

    public function trackShipment(string $awb): array
    {
        // Mock Implementation
        return [
            ['status' => 'Pick Up Scheduled', 'time' => now()->toDateTimeString()],
        ];
    }

    public function cancelShipment(string $awb): bool
    {
        // Mock Implementation
        return true;
    }

    public function generateLabel(string $awb): string
    {
        return $this->baseUrl . "/api/p/packing_slip?wbns=$awb";
    }
}
