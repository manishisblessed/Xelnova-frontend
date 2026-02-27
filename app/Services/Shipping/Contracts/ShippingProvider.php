<?php

namespace App\Services\Shipping\Contracts;

use App\Models\SubOrder;
use App\Models\Seller;

interface ShippingProvider
{
    /**
     * Calculate shipping rates for a sub-order
     * 
     * @param SubOrder $order
     * @param Seller $seller
     * @return array List of rates 
     * [
     *   [
     *     'provider' => 'delhivery', 
     *     'service_type' => 'Surface', 
     *     'price' => 100.00, 
     *     'estimated_date' => '2023-01-01',
     *     'meta_data' => [...] 
     *   ]
     * ]
     */
    public function calculateRates(SubOrder $order, Seller $seller): array;

    /**
     * Create a shipment (book)
     * 
     * @param SubOrder $order
     * @param Seller $seller
     * @param string $serviceCode
     * @return array|null Returns shipment data on success or null
     */
    public function createShipment(SubOrder $order, Seller $seller, string $serviceCode): ?array;

    /**
     * Track a shipment
     */
    public function trackShipment(string $awb): array;

    /**
     * Cancel a shipment
     */
    public function cancelShipment(string $awb): bool;
}
