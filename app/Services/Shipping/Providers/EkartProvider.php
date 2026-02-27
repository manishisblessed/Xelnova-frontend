<?php

namespace App\Services\Shipping\Providers;

use App\Models\Seller;
use App\Models\SubOrder;
use App\Services\Shipping\Contracts\ShippingProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EkartProvider implements ShippingProvider
{
    private const BASE_URL = 'https://app.elite.ekartlogistics.in';
    private const TIMEOUT_SECONDS = 15;
    private const BILLING_CLIENT_TYPE = 'EXISTING_CLIENT';

    protected string $clientId;
    protected string $username;
    protected string $password;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->clientId = (string) config('services.ekart.client_id', '');
        $this->username = (string) config('services.ekart.username', '');
        $this->password = (string) config('services.ekart.password', '');
        $this->baseUrl = self::BASE_URL;
        $this->timeout = self::TIMEOUT_SECONDS;
    }

    public function calculateRates(SubOrder $order, Seller $seller): array
    {
        $pickupPincode = $seller->postal_code;
        $destPincode = data_get($order->order->shipping_address, 'pincode');

        if (empty($pickupPincode) || empty($destPincode)) {
            Log::warning('Ekart rates skipped because pincode is missing', [
                'sub_order_id' => $order->id,
                'pickup_pincode' => $pickupPincode,
                'drop_pincode' => $destPincode,
            ]);
            return [];
        }

        $metrics = $this->calculatePackageMetrics($order);
        $paymentMode = $this->resolvePaymentMode($order);
        $invoiceAmount = (float) ($order->total ?? 0);
        $codAmount = $paymentMode === 'COD' ? round($invoiceAmount, 2) : 0;
        $rates = [];

        $serviceTypes = [
            'SURFACE' => 'Surface',
            'EXPRESS' => 'Express',
        ];

        foreach ($serviceTypes as $serviceCode => $serviceName) {
            if ($this->isServiceTemporarilyDisabled($serviceCode)) {
                continue;
            }

            try {
                $payload = [
                    'billingClientType' => $this->resolveBillingClientType(),
                    'shippingDirection' => 'FORWARD',
                    'serviceType' => $serviceCode,
                    'pickupPincode' => (int) $pickupPincode,
                    'dropPincode' => (int) $destPincode,
                    'invoiceAmount' => round(max($invoiceAmount, 1), 2),
                    'weight' => $metrics['weight'],
                    'length' => $metrics['length'],
                    'height' => $metrics['height'],
                    'width' => $metrics['width'],
                    'codAmount' => $codAmount,
                    'paymentMode' => $paymentMode,
                ];

                $response = $this->authorizedRequest()->post($this->baseUrl . '/data/pricing/estimate', $payload);

                if (!$response->successful()) {
                    if ($this->handleKnownRateCardMissing($response->status(), $response->body(), $serviceCode, $order->id)) {
                        continue;
                    }

                    Log::warning('Ekart rate call failed', [
                        'sub_order_id' => $order->id,
                        'service_type' => $serviceCode,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    continue;
                }

                $data = $response->json() ?? [];
                $price = $this->parseRatePrice($data);

                if ($price <= 0) {
                    continue;
                }

                $rates[] = [
                    'provider' => 'ekart',
                    'service_type' => $serviceName,
                    'price' => $price,
                    'estimated_date' => now()->addDays($serviceCode === 'EXPRESS' ? 2 : 5)->format('Y-m-d'),
                    'meta_data' => [
                        'service_code' => $serviceCode,
                        'raw' => $data,
                    ],
                ];
            } catch (\Throwable $e) {
                Log::error('Ekart rate calculation failed', [
                    'sub_order_id' => $order->id,
                    'service_type' => $serviceCode,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $rates;
    }

    public function createShipment(SubOrder $order, Seller $seller, string $serviceCode): ?array
    {
        return $this->createShipmentWithRecovery($order, $seller, $serviceCode, true);
    }

    protected function createShipmentWithRecovery(SubOrder $order, Seller $seller, string $serviceCode, bool $allowAddressRecovery): ?array
    {
        $shippingAddress = $order->order->shipping_address ?? [];
        $missingFields = $this->getMissingRequiredFields($seller, $shippingAddress);

        if (!empty($missingFields)) {
            throw new \Exception('Missing required Ekart fields: ' . implode(', ', $missingFields));
        }

        $pickupAlias = $this->resolvePickupAlias($seller);
        $metrics = $this->calculatePackageMetrics($order);
        $paymentMode = $this->resolvePaymentMode($order);
        $totalAmount = round((float) ($order->total ?? 0), 2);
        $taxValue = round((float) ($order->tax ?? $order->items->sum('tax_amount')), 2);
        $taxableAmount = round(max($totalAmount - max($taxValue, 0), 1), 2);
        $codAmount = $paymentMode === 'COD' ? round(min($totalAmount, 49999), 2) : 0;
        $trackingPhone = $this->normalizePhone((string) data_get($shippingAddress, 'phone'));
        $sellerPhone = $this->normalizePhone((string) $seller->phone);

        $payload = [
            'tax_value' => max($taxValue, 0),
            'seller_name' => (string) $seller->business_name,
            'seller_address' => (string) $seller->business_address,
            'seller_gst_tin' => (string) $seller->gst_number,
            'seller_gst_amount' => 0,
            'consignee_gst_amount' => 0,
            'integrated_gst_amount' => 0,
            'order_number' => (string) $order->sub_order_number,
            'invoice_number' => (string) $order->sub_order_number,
            'invoice_date' => optional($order->created_at)->format('Y-m-d') ?: now()->format('Y-m-d'),
            'consignee_name' => (string) data_get($shippingAddress, 'name'),
            'consignee_alternate_phone' => $trackingPhone,
            'payment_mode' => $paymentMode,
            'category_of_goods' => 'General',
            'products_desc' => $metrics['description'],
            'total_amount' => max($totalAmount, 1),
            'cod_amount' => $codAmount,
            'taxable_amount' => $taxableAmount,
            'commodity_value' => (string) $taxableAmount,
            'return_reason' => '',
            'quantity' => $metrics['quantity'],
            'weight' => $metrics['weight'],
            'length' => $metrics['length'],
            'height' => $metrics['height'],
            'width' => $metrics['width'],
            'drop_location' => [
                'address' => trim((string) data_get($shippingAddress, 'address_line_1') . ' ' . (string) data_get($shippingAddress, 'address_line_2', '')),
                'city' => (string) data_get($shippingAddress, 'city'),
                'state' => (string) data_get($shippingAddress, 'state'),
                'country' => 'India',
                'name' => (string) data_get($shippingAddress, 'name'),
                'phone' => (int) $trackingPhone,
                'pin' => (int) $this->normalizePincode((string) data_get($shippingAddress, 'pincode')),
            ],
            'pickup_location' => [
                'address' => (string) $seller->business_address,
                'city' => (string) $seller->city,
                'state' => (string) $seller->state,
                'country' => 'India',
                'name' => $pickupAlias,
                'phone' => (int) $sellerPhone,
                'pin' => (int) $this->normalizePincode((string) $seller->postal_code),
            ],
            'return_location' => [
                'address' => (string) $seller->business_address,
                'city' => (string) $seller->city,
                'state' => (string) $seller->state,
                'country' => 'India',
                'name' => $pickupAlias,
                'phone' => (int) $sellerPhone,
                'pin' => (int) $this->normalizePincode((string) $seller->postal_code),
            ],
        ];

        $response = $this->authorizedRequest()->put($this->baseUrl . '/api/v1/package/create', $payload);

        if (!$response->successful()) {
            $body = $response->body();
            if ($allowAddressRecovery && $this->isMissingPickupLocationError($response->status(), $body)) {
                Log::info('Ekart pickup location missing. Attempting address registration and single retry.', [
                    'sub_order_id' => $order->id,
                    'pickup_alias' => $pickupAlias,
                ]);

                $this->ensurePickupAddressExists($seller, $pickupAlias);
                return $this->createShipmentWithRecovery($order, $seller, $serviceCode, false);
            }

            throw new \Exception('Ekart API connection failed: ' . $this->extractProviderErrorMessage($body));
        }

        $data = $response->json() ?? [];
        $status = (bool) data_get($data, 'status', false);
        $trackingId = (string) data_get($data, 'tracking_id', '');

        if (!$status || $trackingId === '') {
            $remark = data_get($data, 'remark', 'Unknown Error');
            throw new \Exception('Ekart Error: ' . $remark);
        }

        return [
            'provider' => 'ekart',
            'awb_code' => $trackingId,
            'provider_order_id' => data_get($data, 'barcodes.order', $trackingId),
            'service_type' => strtoupper($serviceCode) === 'EXPRESS' ? 'Express' : 'Surface',
            'courier_name' => 'Ekart',
            'label_url' => $this->baseUrl . '/track/' . $trackingId,
            'status' => 'pending',
            'meta_data' => $data,
            'tracking_history' => [],
        ];
    }

    public function trackShipment(string $awb): array
    {
        return [];
    }

    public function cancelShipment(string $awb): bool
    {
        return false;
    }

    protected function authorizedRequest()
    {
        $token = $this->getAccessToken();

        return Http::timeout($this->timeout)
            ->acceptJson()
            ->withToken($token);
    }

    protected function getAccessToken(): string
    {
        $this->validateConfig();

        $cacheKey = 'shipping.ekart.token.' . md5($this->clientId . '|' . $this->username);
        $cachedToken = Cache::get($cacheKey);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $url = $this->baseUrl . '/integrations/v2/auth/token/' . $this->clientId;
        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->post($url, [
                'username' => $this->username,
                'password' => $this->password,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to authenticate with Ekart: ' . $response->body());
        }

        $data = $response->json() ?? [];
        $token = (string) data_get($data, 'access_token', '');
        $expiresIn = (int) data_get($data, 'expires_in', 3600);

        if ($token === '') {
            throw new \Exception('Ekart authentication response missing access token');
        }

        $ttlSeconds = max(60, $expiresIn - 300);
        Cache::put($cacheKey, $token, now()->addSeconds($ttlSeconds));

        return $token;
    }

    protected function validateConfig(): void
    {
        $missing = [];

        if ($this->clientId === '') {
            $missing[] = 'EKART_CLIENT_ID';
        }
        if ($this->username === '') {
            $missing[] = 'EKART_USERNAME';
        }
        if ($this->password === '') {
            $missing[] = 'EKART_PASSWORD';
        }

        if (!empty($missing)) {
            throw new \Exception('Ekart configuration missing: ' . implode(', ', $missing));
        }
    }

    protected function parseRatePrice(array $response): float
    {
        $total = data_get($response, 'total');
        if (is_numeric($total)) {
            return (float) $total;
        }

        $shippingCharge = data_get($response, 'shippingCharge');
        if (is_numeric($shippingCharge)) {
            return (float) $shippingCharge;
        }

        return 0.0;
    }

    protected function calculatePackageMetrics(SubOrder $order): array
    {
        $weight = 0;
        $maxLength = 10;
        $maxWidth = 10;
        $maxHeight = 10;
        $quantity = 0;
        $descriptions = [];

        foreach ($order->items as $item) {
            $itemQuantity = max(1, (int) $item->quantity);
            $quantity += $itemQuantity;
            $descriptions[] = (string) ($item->product_name ?: 'Item');

            $product = $item->product;
            if (!$product) {
                $weight += 500 * $itemQuantity;
                continue;
            }

            $weight += ((float) ($product->weight ?? 500)) * $itemQuantity;
            $maxLength = max($maxLength, (float) ($product->length ?? 10));
            $maxWidth = max($maxWidth, (float) ($product->width ?? 10));
            $maxHeight = max($maxHeight, (float) ($product->height ?? 10));
        }

        $productDescription = trim(implode(', ', array_filter($descriptions)));
        if ($productDescription === '') {
            $productDescription = 'Order ' . $order->sub_order_number;
        }

        return [
            'weight' => max(1, (int) ceil($weight > 0 ? $weight : 500)),
            'length' => max(1, (int) ceil($maxLength)),
            'width' => max(1, (int) ceil($maxWidth)),
            'height' => max(1, (int) ceil($maxHeight)),
            'quantity' => max(1, $quantity),
            'description' => substr($productDescription, 0, 250),
        ];
    }

    protected function resolvePaymentMode(SubOrder $order): string
    {
        return strtolower((string) $order->order->payment_method) === 'cod' ? 'COD' : 'Prepaid';
    }

    protected function resolvePickupAlias(Seller $seller): string
    {
        $alias = trim((string) $seller->business_name);
        if ($alias === '') {
            $alias = 'seller-' . ((string) ($seller->user_id ?? $seller->id ?? 'address'));
        }

        return substr($alias, 0, 150);
    }

    protected function ensurePickupAddressExists(Seller $seller, string $alias): void
    {
        $sellerPhone = $this->normalizePhone((string) $seller->phone);
        $payload = [
            'alias' => $alias,
            'phone' => (int) $sellerPhone,
            'address_line1' => substr((string) $seller->business_address, 0, 250),
            'address_line2' => null,
            'pincode' => (int) $this->normalizePincode((string) $seller->postal_code),
            'city' => $seller->city ?: null,
            'state' => $seller->state ?: null,
            'country' => 'India',
        ];

        $response = $this->authorizedRequest()->post($this->baseUrl . '/api/v2/address', $payload);
        $data = $response->json() ?? [];

        if ($response->successful() && (bool) data_get($data, 'status', false)) {
            return;
        }

        $body = $response->body();
        if ($this->addressAliasExists($alias)) {
            return;
        }

        throw new \Exception('Unable to register Ekart pickup location: ' . $this->extractProviderErrorMessage($body));
    }

    protected function addressAliasExists(string $alias): bool
    {
        try {
            $response = $this->authorizedRequest()->get($this->baseUrl . '/api/v2/addresses');
            if (!$response->successful()) {
                return false;
            }

            $items = $response->json();
            if (!is_array($items)) {
                return false;
            }

            foreach ($items as $item) {
                $itemAlias = trim((string) data_get($item, 'alias', ''));
                if ($itemAlias !== '' && strcasecmp($itemAlias, $alias) === 0) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed checking Ekart address alias existence', [
                'alias' => $alias,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    protected function isMissingPickupLocationError(int $status, string $body): bool
    {
        if ($status !== 404) {
            return false;
        }

        $message = strtolower($body);
        return str_contains($message, 'pickup_location')
            && (str_contains($message, 'does not exist') || str_contains($message, 'is deleted'));
    }

    protected function extractProviderErrorMessage(string $body): string
    {
        $json = json_decode($body, true);
        if (is_array($json)) {
            $description = data_get($json, 'description');
            if (is_string($description) && trim($description) !== '') {
                return $description;
            }

            $message = data_get($json, 'message');
            if (is_string($message) && trim($message) !== '') {
                return $message;
            }

            $remark = data_get($json, 'remark');
            if (is_string($remark) && trim($remark) !== '') {
                return $remark;
            }
        }

        return $body;
    }

    protected function isServiceTemporarilyDisabled(string $serviceCode): bool
    {
        return (bool) Cache::get($this->serviceDisabledCacheKey($serviceCode), false);
    }

    protected function serviceDisabledCacheKey(string $serviceCode): string
    {
        return 'shipping.ekart.rate_service_disabled.' . md5($this->clientId) . '.' . strtoupper($serviceCode);
    }

    protected function handleKnownRateCardMissing(int $status, string $body, string $serviceCode, int $subOrderId): bool
    {
        if ($status !== 404) {
            return false;
        }

        $message = strtolower($body);
        if (!str_contains($message, 'could not find a rate card')
            || !str_contains($message, 'matching weight brackets')) {
            return false;
        }

        Cache::put($this->serviceDisabledCacheKey($serviceCode), true, now()->addHours(12));

        Log::info('Ekart service type disabled temporarily because no matching rate card is configured', [
            'sub_order_id' => $subOrderId,
            'service_type' => strtoupper($serviceCode),
            'disabled_for_hours' => 12,
        ]);

        return true;
    }

    protected function resolveBillingClientType(): string
    {
        return self::BILLING_CLIENT_TYPE;
    }

    protected function getMissingRequiredFields(Seller $seller, array $shippingAddress): array
    {
        $required = [
            'seller.business_name' => $seller->business_name,
            'seller.business_address' => $seller->business_address,
            'seller.city' => $seller->city,
            'seller.state' => $seller->state,
            'seller.postal_code' => $seller->postal_code,
            'seller.phone' => $seller->phone,
            'seller.gst_number' => $seller->gst_number,
            'shipping.name' => data_get($shippingAddress, 'name'),
            'shipping.address_line_1' => data_get($shippingAddress, 'address_line_1'),
            'shipping.city' => data_get($shippingAddress, 'city'),
            'shipping.state' => data_get($shippingAddress, 'state'),
            'shipping.pincode' => data_get($shippingAddress, 'pincode'),
            'shipping.phone' => data_get($shippingAddress, 'phone'),
        ];

        $missing = [];
        foreach ($required as $field => $value) {
            if ($value === null || trim((string) $value) === '') {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if (strlen($digits) >= 10) {
            return substr($digits, -10);
        }

        return str_pad($digits, 10, '0');
    }

    protected function normalizePincode(string $pincode): string
    {
        $digits = preg_replace('/\D+/', '', $pincode) ?: '';

        if (strlen($digits) >= 6) {
            return substr($digits, 0, 6);
        }

        return str_pad($digits, 6, '0');
    }
}
