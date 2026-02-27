<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class UnifiedPermissionSeeder extends Seeder
{
    /**
     * Unified permission mapping following the convention: moduleName_permissionName
     * - Module names: camelCase, no underscores
     * - Permission names: camelCase, no underscores
     * - Single underscore separator between module and permission
     */
    protected array $permissions = [
        // User Management
        'permission' => ['list', 'create', 'edit', 'delete'],
        'role' => ['list', 'create', 'edit', 'delete', 'export'],
        'user' => ['list', 'create', 'edit', 'delete', 'export'],
        'signinLog' => ['list', 'delete', 'export', 'view'],
        'activityLog' => ['list', 'delete', 'export'],
        
        // Ecommerce Modules
        'seller' => ['list', 'view', 'approve', 'suspend', 'delete'],
        'sellerBrand' => ['list', 'view', 'approve', 'reject', 'delete'],
        'customer' => ['list', 'view', 'edit', 'delete'],
        'category' => ['list', 'create', 'edit', 'delete'],
        'product' => ['list', 'view', 'create', 'edit', 'approve', 'reject', 'delete'],
        'brand' => ['list', 'create', 'edit', 'delete'],
        'variantType' => ['list', 'create', 'edit', 'delete'],
        'order' => ['list', 'view', 'update', 'cancel'],
        'dispute' => ['list', 'view', 'resolve'],
        'refund' => ['list', 'approve', 'reject'],
        'commission' => ['view', 'edit'],
        'payout' => ['list', 'process', 'view'],
        'coupon' => ['list', 'create', 'edit', 'delete'],
        'flashDeal' => ['list', 'create', 'edit', 'delete'],
        'featuredProduct' => ['list', 'create', 'delete'],
        'banner' => ['list', 'create', 'edit', 'delete'],
        'page' => ['list', 'create', 'edit', 'delete'],
        'salesReport' => ['view', 'export'],
        'sellerPerformance' => ['view', 'export'],
        'revenueReport' => ['view', 'export'],
        'sellerFinance' => ['request'],
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Generate all permissions following the convention
        $allPermissions = [];
        foreach ($this->permissions as $module => $actions) {
            foreach ($actions as $action) {
                $allPermissions[] = $module . '_' . $action;
            }
        }

        // Create permissions
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
