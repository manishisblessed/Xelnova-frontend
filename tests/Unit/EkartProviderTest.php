<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Services\Shipping\Providers\EkartProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EkartProviderTest extends TestCase
{
    public function test_it_fetches_and_uses_cached_token_for_estimate_calls(): void
    {
        $this->configureEkart();

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/integrations/v2/auth/token/')) {
                return Http::response([
                    'access_token' => 'token-1',
                    'token_type' => 'Bearer',
                    'expires_in' => 3600,
                ], 200);
            }

            if (str_contains($request->url(), '/data/pricing/estimate')) {
                return Http::response(['total' => '149.00'], 200);
            }

            return Http::response([], 404);
        });

        $provider = new EkartProvider();
        $ratesA = $provider->calculateRates($this->makeSubOrder(), $this->makeSeller());
        $ratesB = $provider->calculateRates($this->makeSubOrder(), $this->makeSeller());

        $this->assertCount(2, $ratesA);
        $this->assertCount(2, $ratesB);

        $recorded = collect(Http::recorded());
        $authCalls = $recorded
            ->filter(fn ($pair) => str_contains($pair[0]->url(), '/integrations/v2/auth/token/'))
            ->count();
        $estimateCalls = $recorded
            ->filter(fn ($pair) => str_contains($pair[0]->url(), '/data/pricing/estimate'))
            ->count();

        $this->assertSame(1, $authCalls);
        $this->assertSame(4, $estimateCalls);
    }

    public function test_it_returns_surface_and_express_rates_in_normalized_format(): void
    {
        $this->configureEkart();

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/integrations/v2/auth/token/')) {
                return Http::response([
                    'access_token' => 'token-2',
                    'token_type' => 'Bearer',
                    'expires_in' => 3600,
                ], 200);
            }

            if (str_contains($request->url(), '/data/pricing/estimate')) {
                $payload = $request->data();
                $serviceType = $payload['serviceType'] ?? null;

                if ($serviceType === 'SURFACE') {
                    return Http::response(['total' => '99.00', 'shippingCharge' => '80.00'], 200);
                }

                return Http::response(['total' => '149.00', 'shippingCharge' => '120.00'], 200);
            }

            return Http::response([], 404);
        });

        $provider = new EkartProvider();
        $rates = $provider->calculateRates($this->makeSubOrder(), $this->makeSeller());

        $this->assertCount(2, $rates);
        $this->assertSame('ekart', $rates[0]['provider']);
        $this->assertSame('Surface', $rates[0]['service_type']);
        $this->assertSame('SURFACE', $rates[0]['meta_data']['service_code']);
        $this->assertEquals(99.00, $rates[0]['price']);
        $this->assertSame('Express', $rates[1]['service_type']);
        $this->assertSame('EXPRESS', $rates[1]['meta_data']['service_code']);
        $this->assertEquals(149.00, $rates[1]['price']);
    }

    public function test_it_blocks_booking_with_clear_error_when_required_fields_missing(): void
    {
        $this->configureEkart();
        Http::fake();

        $provider = new EkartProvider();
        $subOrder = $this->makeSubOrder();
        $seller = $this->makeSeller(['gst_number' => null]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing required Ekart fields');

        try {
            $provider->createShipment($subOrder, $seller, 'SURFACE');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_it_maps_successful_create_response_to_shipment_payload(): void
    {
        $this->configureEkart();

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/integrations/v2/auth/token/')) {
                return Http::response([
                    'access_token' => 'token-3',
                    'token_type' => 'Bearer',
                    'expires_in' => 3600,
                ], 200);
            }

            if (str_contains($request->url(), '/api/v1/package/create')) {
                return Http::response([
                    'status' => true,
                    'remark' => 'Successfully created shipment',
                    'tracking_id' => '500999A3408005',
                    'vendor' => 'XYZ',
                    'barcodes' => [
                        'wbn' => 'WBN123',
                        'order' => 'ORD-100-01',
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $provider = new EkartProvider();
        $shipment = $provider->createShipment($this->makeSubOrder(), $this->makeSeller(), 'EXPRESS');

        $this->assertSame('ekart', $shipment['provider']);
        $this->assertSame('500999A3408005', $shipment['awb_code']);
        $this->assertSame('ORD-100-01', $shipment['provider_order_id']);
        $this->assertSame('Express', $shipment['service_type']);
        $this->assertSame('Ekart', $shipment['courier_name']);
        $this->assertSame('https://app.elite.ekartlogistics.in/track/500999A3408005', $shipment['label_url']);

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), '/api/v1/package/create')) {
                return false;
            }

            $payload = $request->data();

            return $request->method() === 'PUT'
                && data_get($payload, 'payment_mode') === 'Prepaid'
                && data_get($payload, 'seller_name') === 'Test Seller'
                && data_get($payload, 'drop_location.pin') === 560001
                && data_get($payload, 'pickup_location.name') === 'Test Seller'
                && data_get($payload, 'pickup_location.pin') === 110001;
        });
    }

    public function test_it_registers_pickup_address_and_retries_booking_when_pickup_location_is_missing(): void
    {
        $this->configureEkart();

        $createCalls = 0;
        $addressCalls = 0;

        Http::fake(function ($request) use (&$createCalls, &$addressCalls) {
            if (str_contains($request->url(), '/integrations/v2/auth/token/')) {
                return Http::response([
                    'access_token' => 'token-4',
                    'token_type' => 'Bearer',
                    'expires_in' => 3600,
                ], 200);
            }

            if (str_contains($request->url(), '/api/v2/address')) {
                $addressCalls++;

                return Http::response([
                    'status' => true,
                    'alias' => 'Test Seller',
                    'remark' => 'Address created',
                ], 200);
            }

            if (str_contains($request->url(), '/api/v1/package/create')) {
                $createCalls++;

                if ($createCalls === 1) {
                    return Http::response([
                        'statusCode' => 404,
                        'code' => 'pickup_missing',
                        'message' => 'SWIFT_RESOURCE_NOT_FOUND_EXCEPTION',
                        'description' => 'Test Seller pickup_location does not exist or is deleted.',
                        'severity' => 'ERROR',
                    ], 404);
                }

                return Http::response([
                    'status' => true,
                    'remark' => 'Successfully created shipment',
                    'tracking_id' => '500999A9999999',
                    'vendor' => 'XYZ',
                    'barcodes' => [
                        'wbn' => 'WBN999',
                        'order' => 'ORD-100-01',
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $provider = new EkartProvider();
        $shipment = $provider->createShipment($this->makeSubOrder(), $this->makeSeller(), 'SURFACE');

        $this->assertSame('500999A9999999', $shipment['awb_code']);
        $this->assertSame(2, $createCalls);
        $this->assertSame(1, $addressCalls);

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), '/api/v2/address')) {
                return false;
            }

            $payload = $request->data();
            return $request->method() === 'POST'
                && data_get($payload, 'alias') === 'Test Seller'
                && data_get($payload, 'pincode') === 110001;
        });
    }

    protected function configureEkart(): void
    {
        Cache::flush();

        config()->set('services.ekart.client_id', 'EKART_TEST_CLIENT');
        config()->set('services.ekart.username', 'ekart-user');
        config()->set('services.ekart.password', 'ekart-pass');
    }

    protected function makeSeller(array $overrides = []): Seller
    {
        $seller = new Seller(array_merge([
            'business_name' => 'Test Seller',
            'business_address' => '123 Test Street',
            'city' => 'Delhi',
            'state' => 'DL',
            'postal_code' => '110001',
            'phone' => '9876543210',
            'gst_number' => '22AAAAA0000A1Z5',
        ], $overrides));

        return $seller;
    }

    protected function makeSubOrder(array $overrides = []): SubOrder
    {
        $order = new Order();
        $order->shipping_address = [
            'name' => 'Rahul Sharma',
            'address_line_1' => 'Plot 9, MG Road',
            'address_line_2' => 'Near Metro',
            'city' => 'Bengaluru',
            'state' => 'KA',
            'pincode' => '560001',
            'phone' => '9988776655',
        ];
        $order->payment_method = $overrides['payment_method'] ?? 'prepaid';
        $order->order_number = 'ORD-100';

        $subOrder = new SubOrder(array_merge([
            'sub_order_number' => 'ORD-100-01',
            'seller_id' => 22,
            'subtotal' => 1000,
            'shipping_charge' => 50,
            'tax' => 100,
            'total' => 1150,
            'status' => 'processing',
        ], $overrides));
        $subOrder->created_at = now();
        $subOrder->setRelation('order', $order);

        $product = new Product([
            'weight' => 1200,
            'length' => 12,
            'width' => 10,
            'height' => 6,
        ]);

        $item = new OrderItem([
            'product_name' => 'Demo Product',
            'quantity' => 1,
            'tax_amount' => 100,
        ]);
        $item->setRelation('product', $product);

        $subOrder->setRelation('items', collect([$item]));

        return $subOrder;
    }
}
