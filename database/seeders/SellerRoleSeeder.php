<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SellerRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create seller role if it doesn't exist
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);

        // Define seller permissions
        $permissions = [
            'seller_dashboard_view',
            'seller_products_view',
            'seller_products_create',
            'seller_products_edit',
            'seller_products_delete',
            'seller_orders_view',
            'seller_orders_update',
            'seller_finance_view',
            'seller_finance_request',
            'seller_documents_view',
            'seller_documents_upload',
            'seller_documents_delete',
            'seller_bank_accounts_view',
            'seller_bank_accounts_create',
            'seller_bank_accounts_edit',
            'seller_bank_accounts_delete',
        ];

        // Create permissions and assign to seller role
        foreach ($permissions as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission]);
            $sellerRole->givePermissionTo($perm);
        }

        $this->command->info('Seller role and permissions created successfully!');
    }
}
