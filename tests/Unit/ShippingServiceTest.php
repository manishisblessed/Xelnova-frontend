<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Seller;
use App\Models\SubOrder;
use App\Services\Shipping\Contracts\ShippingProvider;
use App\Services\Shipping\ShippingService;
use Mockery;
use Tests\TestCase;

class ShippingServiceTest extends TestCase
{
    public function test_it_resolves_delhivery_provider()
    {
        $service = new ShippingService();
        $provider = $service->getProvider('delhivery');
        
        $this->assertInstanceOf(\App\Services\Shipping\Providers\DelhiveryProvider::class, $provider);
    }

    public function test_it_resolves_ekart_provider()
    {
        $service = new ShippingService();
        $provider = $service->getProvider('ekart');

        $this->assertInstanceOf(\App\Services\Shipping\Providers\EkartProvider::class, $provider);
    }

    public function test_it_aggregates_rates_from_multiple_providers_with_partial_success()
    {
        $order = new Order();
        $order->shipping_address = ['pincode' => '560001'];
        $order->payment_method = 'prepaid';

        $seller = new Seller();
        $seller->postal_code = '110001';

        $subOrder = new SubOrder();
        $subOrder->id = 1;
        $subOrder->seller_id = 100;
        $subOrder->sub_order_number = 'ORD-100-01';
        $subOrder->setRelation('order', $order);
        $subOrder->setRelation('items', collect());
        $subOrder->setRelation('sellerProfile', $seller);

        $providers = [
            'delhivery' => new class implements ShippingProvider {
                public function calculateRates(SubOrder $order, Seller $seller): array
                {
                    return [[
                        'provider' => 'delhivery',
                        'service_type' => 'Surface',
                        'price' => 220,
                        'estimated_date' => now()->format('Y-m-d'),
                        'meta_data' => ['service_code' => 'S'],
                    ]];
                }

                public function createShipment(SubOrder $order, Seller $seller, string $serviceCode): ?array
                {
                    return null;
                }

                public function trackShipment(string $awb): array
                {
                    return [];
                }

                public function cancelShipment(string $awb): bool
                {
                    return false;
                }
            },
            'ekart' => new class implements ShippingProvider {
                public function calculateRates(SubOrder $order, Seller $seller): array
                {
                    throw new \Exception('Provider unavailable');
                }

                public function createShipment(SubOrder $order, Seller $seller, string $serviceCode): ?array
                {
                    return null;
                }

                public function trackShipment(string $awb): array
                {
                    return [];
                }

                public function cancelShipment(string $awb): bool
                {
                    return false;
                }
            },
            'local' => new class implements ShippingProvider {
                public function calculateRates(SubOrder $order, Seller $seller): array
                {
                    return [[
                        'provider' => 'local',
                        'service_type' => 'Standard',
                        'price' => 110,
                        'estimated_date' => now()->format('Y-m-d'),
                        'meta_data' => ['service_code' => 'STD'],
                    ]];
                }

                public function createShipment(SubOrder $order, Seller $seller, string $serviceCode): ?array
                {
                    return null;
                }

                public function trackShipment(string $awb): array
                {
                    return [];
                }

                public function cancelShipment(string $awb): bool
                {
                    return false;
                }
            },
        ];

        $service = new class($providers) extends ShippingService {
            public function __construct(private array $providers)
            {
            }

            public function getConfiguredProviders(): array
            {
                return array_keys($this->providers);
            }

            public function getProvider(string $providerName = 'delhivery'): ShippingProvider
            {
                $providerName = strtolower(trim($providerName));

                if (!isset($this->providers[$providerName])) {
                    throw new \Exception("Shipping provider {$providerName} not supported");
                }

                return $this->providers[$providerName];
            }
        };

        $rates = $service->getRates($subOrder);

        $this->assertCount(2, $rates);
        $this->assertSame('local', $rates[0]['provider']);
        $this->assertSame('delhivery', $rates[1]['provider']);
    }

    public function test_it_throws_when_no_shipping_providers_are_configured()
    {
        config()->set('services.shipping.providers', []);

        $order = new Order();
        $order->shipping_address = ['pincode' => '560001'];
        $order->payment_method = 'prepaid';

        $seller = new Seller();
        $seller->postal_code = '110001';

        $subOrder = new SubOrder();
        $subOrder->id = 1;
        $subOrder->seller_id = 100;
        $subOrder->sub_order_number = 'ORD-100-01';
        $subOrder->setRelation('order', $order);
        $subOrder->setRelation('items', collect());
        $subOrder->setRelation('sellerProfile', $seller);

        $service = new ShippingService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No shipping providers configured');

        $service->getRates($subOrder);
    }

    public function test_it_returns_mock_rates()
    {
        // Mock Http Response
        \Illuminate\Support\Facades\Http::fake([
            '*/api/kinko/v1/invoice/charges/*' => \Illuminate\Support\Facades\Http::response([
                [
                    'total_amount' => 150.00,
                    'rate' => 150.00
                ]
            ], 200),
            '*/c/api/pin-codes/json/*' => \Illuminate\Support\Facades\Http::response([
                'delivery_codes' => []
            ], 200)
        ]);

        // Create Real Objects
        $seller = Mockery::mock(Seller::class);
        $seller->shouldReceive('getAttribute')->with('postal_code')->andReturn('110001');
        
        $order = new Order();
        $order->id = 1;
        $order->shipping_address = ['pincode' => '560001'];
        $order->order_number = 'ORD-001';

        $subOrder = new SubOrder();
        $subOrder->id = 1;
        $subOrder->seller_id = 1;
        $subOrder->sub_order_number = 'ORD-001-01';
        $subOrder->setRelation('order', $order);

        $item1 = new \App\Models\OrderItem();
        $item1->quantity = 2;
        $product = new \App\Models\Product();
        $product->weight = 1000;
        $item1->setRelation('product', $product);

        $subOrder->setRelation('items', collect([$item1]));
        
        $provider = new \App\Services\Shipping\Providers\DelhiveryProvider();
        
        $rates = $provider->calculateRates($subOrder, $seller);
        
        $this->assertIsArray($rates);
        $this->assertNotEmpty($rates);
        $this->assertEquals('delhivery', $rates[0]['provider']);
        $this->assertEquals(150.00, $rates[0]['price']);
    }
}
