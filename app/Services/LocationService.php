<?php

namespace App\Services;

use App\Models\PincodeLocation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationService
{
    /**
     * Get latitude and longitude for a pincode.
     * Checks database first, then fetches from API.
     *
     * @param string $pincode
     * @param string $countryCode
     * @return array|null ['lat' => float, 'lng' => float, 'city' => string, 'state' => string]
     */
    public function getPincodeCoordinates(string $pincode, string $countryCode = 'IN')
    {
        // 1. Check Database Cache
        $location = PincodeLocation::where('pincode', $pincode)
            ->where('country', $countryCode)
            ->first();

        if ($location) {
            return [
                'lat' => $location->latitude,
                'lng' => $location->longitude,
                'city' => $location->city,
                'state' => $location->state,
            ];
        }

        // 2. Fetch from Nominatim (OpenStreetMap) - Better coverage for India
        try {
            // Respect Nominatim Usage Policy: Provide a User-Agent
            $response = Http::withHeaders([
                'User-Agent' => config('app.name', 'Xelnova') . '/1.0',
            ])->get("https://nominatim.openstreetmap.org/search", [
                'postalcode' => $pincode,
                'country' => 'India',
                'format' => 'json'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data[0])) {
                    $place = $data[0];
                    $lat = (float) $place['lat'];
                    $lng = (float) $place['lon'];
                    
                    // Extract city/state from display_name if possible, or leave as null/generic
                    // Nominatim display_name format: "Code, Area, District, State, Country"
                    $parts = explode(',', $place['display_name']);
                    $city = trim($parts[1] ?? ''); // Rough estimate
                    $state = trim($parts[count($parts) - 3] ?? '');

                    // 3. Cache Result
                    PincodeLocation::create([
                        'pincode' => $pincode,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'city' => $city,
                        'state' => $state,
                        'country' => $countryCode,
                    ]);

                    return [
                        'lat' => $lat,
                        'lng' => $lng,
                        'city' => $city,
                        'state' => $state,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch coordinates for pincode {$pincode}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Calculate distance between two coordinates in Kilometers.
     * Uses Haversine formula.
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in km
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get estimated delivery date based on distance.
     *
     * @param float $distanceKm
     * @return string Formatted date string
     */
    public function getDeliveryEstimate($distanceKm)
    {
        // Fetch delivery settings
        $settings = \App\Models\Setting::where('group', 'delivery')->pluck('value', 'slug');

        // Parse settings or use defaults
        $tier1Dist = (float) ($settings['delivery_tier_1_dist'] ?? 50);
        $tier1Days = (int) ($settings['delivery_tier_1_days'] ?? 1);
        
        $tier2Dist = (float) ($settings['delivery_tier_2_dist'] ?? 200);
        $tier2Days = (int) ($settings['delivery_tier_2_days'] ?? 2);
        
        $tier3Dist = (float) ($settings['delivery_tier_3_dist'] ?? 500);
        $tier3Days = (int) ($settings['delivery_tier_3_days'] ?? 3);
        
        $tier4Days = (int) ($settings['delivery_tier_4_days'] ?? 5);

        // Calculate based on settings
        if ($distanceKm < $tier1Dist) {
            return now()->addDays($tier1Days)->format('D, M d');
        } elseif ($distanceKm < $tier2Dist) {
            return now()->addDays($tier2Days)->format('D, M d');
        } elseif ($distanceKm < $tier3Dist) {
            return now()->addDays($tier3Days)->format('D, M d');
        } else {
            return now()->addDays($tier4Days)->format('D, M d');
        }
    }
}
