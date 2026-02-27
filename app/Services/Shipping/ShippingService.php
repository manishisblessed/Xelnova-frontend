<?php

namespace App\Services\Shipping;

use App\Models\SubOrder;
use App\Models\Shipment;
use App\Models\Seller;
use App\Services\Shipping\Contracts\ShippingProvider;
use App\Services\Shipping\Providers\DelhiveryProvider;
use App\Services\Shipping\Providers\EkartProvider;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    public function getProvider(string $providerName = 'delhivery'): ShippingProvider
    {
        $providerName = strtolower(trim($providerName));

        switch ($providerName) {
            case 'delhivery':
                return new DelhiveryProvider();
            case 'ekart':
                return new EkartProvider();
            default:
                throw new \Exception("Shipping provider {$providerName} not supported");
        }
    }

    public function getConfiguredProviders(): array
    {
        $configured = config('services.shipping.providers', ['delhivery']);

        if (is_string($configured)) {
            $configured = explode(',', $configured);
        }

        if (!is_array($configured)) {
            return [];
        }

        $providers = array_values(array_unique(array_filter(array_map(
            fn ($provider) => strtolower(trim((string) $provider)),
            $configured
        ))));

        return $providers;
    }

    /**
     * Get rates for a sub-order from all available providers (or specific one)
     */
    public function getRates(SubOrder $subOrder): array
    {
        $configuredProviders = $this->getConfiguredProviders();
        if (empty($configuredProviders)) {
            Log::warning('Shipping rates skipped because no shipping providers are configured');
            throw new \Exception('No shipping providers configured. Please set SHIPPING_PROVIDERS in .env');
        }

        $seller = $subOrder->sellerProfile;
        if (!$seller) {
            $seller = Seller::where('user_id', $subOrder->seller_id)->first();
        }

        if (!$seller) {
            Log::warning('Shipping rates skipped because seller profile was not found', [
                'sub_order_id' => $subOrder->id,
                'seller_id' => $subOrder->seller_id,
            ]);
            return [];
        }

        $aggregatedRates = [];
        foreach ($configuredProviders as $providerName) {
            try {
                $providerRates = $this->getProvider($providerName)->calculateRates($subOrder, $seller);

                if (!is_array($providerRates) || empty($providerRates)) {
                    continue;
                }

                $aggregatedRates = array_merge($aggregatedRates, $providerRates);
            } catch (\Throwable $e) {
                Log::error('Shipping rate provider failed', [
                    'provider' => $providerName,
                    'sub_order_id' => $subOrder->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        usort($aggregatedRates, function ($left, $right) {
            $leftPrice = (float) ($left['price'] ?? 0);
            $rightPrice = (float) ($right['price'] ?? 0);
            return $leftPrice <=> $rightPrice;
        });

        return $aggregatedRates;
    }

    /**
     * Book shipment
     */
    public function bookShipment(SubOrder $subOrder, string $providerName, string $serviceCode)
    {
        $provider = $this->getProvider($providerName);
        
        $seller = Seller::where('user_id', $subOrder->seller_id)->first();

        if (!$seller) {
            throw new \Exception('Seller profile not found for shipment booking');
        }

        $shipmentData = $provider->createShipment($subOrder, $seller, $serviceCode);

        if ($shipmentData) {
            // Save to database
            return Shipment::create(array_merge($shipmentData, [
                'sub_order_id' => $subOrder->id,
            ]));
        }

        throw new \Exception("Failed to book shipment with {$providerName}");
    }
}
