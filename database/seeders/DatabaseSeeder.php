<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Use the unified permission seeder instead of multiple individual ones
        $this->call(BasicAdminPermissionSeeder::class);
        $this->call(UnifiedPermissionSeeder::class);
        $this->call(settingSeeder::class);
        $this->call(SellerRoleSeeder::class);
        $this->call(SellerSeeder::class);
        $this->call(MarketplaceSeeder::class);
        $this->call(PageSeeder::class);
        $this->call(PincodeLocationSeeder::class);
    }
}
