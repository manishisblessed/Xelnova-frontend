<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class DeliverySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'slug' => 'delivery_tier_1_dist',
                'label' => 'Tier 1 Distance (km)',
                'value' => '50',
                'vtype' => 'number',
                'group' => 'delivery',
                'access_roles' => 'admin',
            ],
            [
                'slug' => 'delivery_tier_1_days',
                'label' => 'Tier 1 Delivery Days',
                'value' => '1',
                'vtype' => 'number',
                'group' => 'delivery',
                'access_roles' => 'admin',
            ],
            [
                'slug' => 'delivery_tier_2_dist',
                'label' => 'Tier 2 Distance (km)',
                'value' => '200',
                'vtype' => 'number',
                'group' => 'delivery',
                'access_roles' => 'admin',
            ],
            [
                'slug' => 'delivery_tier_2_days',
                'label' => 'Tier 2 Delivery Days',
                'value' => '2',
                'vtype' => 'number',
                'group' => 'delivery',
                'access_roles' => 'admin',
            ],
            [
                'slug' => 'delivery_tier_3_dist',
                'label' => 'Tier 3 Distance (km)',
                'value' => '500',
                'vtype' => 'number',
                'group' => 'delivery',
                'access_roles' => 'admin',
            ],
            [
                'slug' => 'delivery_tier_3_days',
                'label' => 'Tier 3 Delivery Days',
                'value' => '3',
                'vtype' => 'number',
                'group' => 'delivery',
                'access_roles' => 'admin',
            ],
            [
                'slug' => 'delivery_tier_4_days',
                'label' => 'Tier 4 Delivery Days (>Tier 3)',
                'value' => '5',
                'vtype' => 'number',
                'group' => 'delivery',
                'access_roles' => 'admin',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['slug' => $setting['slug']],
                $setting
            );
        }
    }
}
