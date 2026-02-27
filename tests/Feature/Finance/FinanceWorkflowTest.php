<?php

namespace Tests\Feature\Finance;

use App\Models\AdminCommissionEntry;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Seller;
use App\Models\SellerBankAccount;
use App\Models\SellerLedgerEntry;
use App\Models\SellerPayoutRequest;
use App\Models\SubOrder;
use App\Models\User;
use App\Services\Finance\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_create_payout_request_for_full_available_balance(): void
    {
        [$sellerUser, $sellerProfile] = $this->createSeller();
        SellerBankAccount::create([
            'seller_id' => $sellerProfile->id,
            'account_holder_name' => 'Seller One',
            'account_number' => '1234567890',
            'bank_name' => 'HDFC',
            'ifsc_code' => 'HDFC0001234',
            'verification_status' => 'verified',
            'is_primary' => true,
        ]);

        $subOrder = $this->createDeliveredSubOrder($sellerUser->id, 1000);
        app(LedgerService::class)->postDeliveredSale($subOrder);

        $response = $this->actingAs($sellerUser)->post(route('seller.finance.payout-request'), [
            'seller_notes' => 'Please process quickly',
        ]);

        $response->assertRedirect(route('seller.finance'));
        $this->assertDatabaseHas('seller_payout_requests', [
            'seller_id' => $sellerUser->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('seller_payout_request_items', 1);
    }

    public function test_seller_payout_request_blocked_without_verified_bank_or_minimum_amount(): void
    {
        [$sellerUser, $sellerProfile] = $this->createSeller();

        $subOrder = $this->createDeliveredSubOrder($sellerUser->id, 300);
        app(LedgerService::class)->postDeliveredSale($subOrder);

        $response = $this->actingAs($sellerUser)->post(route('seller.finance.payout-request'));
        $response->assertSessionHasErrors();

        SellerBankAccount::create([
            'seller_id' => $sellerProfile->id,
            'account_holder_name' => 'Seller One',
            'account_number' => '1234567890',
            'bank_name' => 'HDFC',
            'ifsc_code' => 'HDFC0001234',
            'verification_status' => 'verified',
            'is_primary' => true,
        ]);

        $response = $this->actingAs($sellerUser)->post(route('seller.finance.payout-request'));
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('seller_payout_requests', 0);
    }

    public function test_admin_can_approve_and_process_payout_request(): void
    {
        [$sellerUser, $sellerProfile] = $this->createSeller();
        SellerBankAccount::create([
            'seller_id' => $sellerProfile->id,
            'account_holder_name' => 'Seller One',
            'account_number' => '1234567890',
            'bank_name' => 'HDFC',
            'ifsc_code' => 'HDFC0001234',
            'verification_status' => 'verified',
            'is_primary' => true,
        ]);

        $subOrder = $this->createDeliveredSubOrder($sellerUser->id, 1000);
        app(LedgerService::class)->postDeliveredSale($subOrder);

        $this->actingAs($sellerUser)->post(route('seller.finance.payout-request'));
        $request = SellerPayoutRequest::firstOrFail();

        $admin = $this->createAdminWithPermissions(['payout_list', 'payout_view', 'payout_process']);

        $this->actingAs($admin)->post(route('payout.approve', $request->id), [
            'notes' => 'Approved',
        ])->assertRedirect();

        $request->refresh();
        $this->assertSame('approved', $request->status);

        $this->actingAs($admin)->post(route('payout.process', $request->id), [
            'payment_reference' => 'UTR12345',
            'payment_method' => 'neft',
            'notes' => 'Paid out',
        ])->assertRedirect();

        $request->refresh();
        $this->assertSame('paid', $request->status);
        $this->assertDatabaseHas('seller_ledger_entries', [
            'payout_request_id' => $request->id,
            'entry_type' => 'payout_debit',
        ]);
        $this->assertNotNull(SubOrder::find($subOrder->id)->payout_at);
    }

    public function test_commission_entries_created_on_delivery_and_refund(): void
    {
        [$sellerUser] = $this->createSeller();
        $subOrder = $this->createDeliveredSubOrder($sellerUser->id, 1000);

        $ledgerService = app(LedgerService::class);
        $ledgerService->postDeliveredSale($subOrder);
        $ledgerService->postRefund($subOrder, 400);

        $this->assertDatabaseHas('admin_commission_entries', [
            'sub_order_id' => $subOrder->id,
            'entry_type' => 'earned',
        ]);
        $this->assertDatabaseHas('admin_commission_entries', [
            'sub_order_id' => $subOrder->id,
            'entry_type' => 'reversed',
        ]);
    }

    public function test_admin_commission_page_loads_with_summary(): void
    {
        [$sellerUser] = $this->createSeller();
        $subOrder = $this->createDeliveredSubOrder($sellerUser->id, 1000);
        app(LedgerService::class)->postDeliveredSale($subOrder);

        $admin = $this->createAdminWithPermissions(['commission_view']);
        $response = $this->actingAs($admin)->get(route('commission.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/CommissionIndexView'));
    }

    public function test_backfill_command_is_idempotent(): void
    {
        [$sellerUser] = $this->createSeller();
        $subOrder = $this->createDeliveredSubOrder($sellerUser->id, 1200);
        $subOrder->update(['payout_at' => now()]);

        $this->artisan('finance:backfill-ledger')->assertExitCode(0);
        $this->artisan('finance:backfill-ledger')->assertExitCode(0);

        $this->assertGreaterThanOrEqual(2, SellerLedgerEntry::count());
        $this->assertSame(1, SellerPayoutRequest::count());
        $this->assertGreaterThanOrEqual(1, AdminCommissionEntry::count());
    }

    protected function createSeller(): array
    {
        $sellerUser = User::factory()->create([
            'user_type' => 'seller',
            'twofa' => 0,
        ]);

        $sellerProfile = Seller::create([
            'user_id' => $sellerUser->id,
            'business_name' => 'Seller ' . $sellerUser->id,
            'business_type' => 'individual',
            'business_address' => 'Address',
            'city' => 'Mumbai',
            'state' => 'MH',
            'postal_code' => '400001',
            'country' => 'India',
            'phone' => '9999999999',
            'email' => 'seller' . $sellerUser->id . '@example.com',
            'status' => 'approved',
            'verification_status' => 'verified',
            'commission_rate' => 10,
        ]);

        return [$sellerUser, $sellerProfile];
    }

    protected function createDeliveredSubOrder(int $sellerId, float $total): SubOrder
    {
        $customer = User::factory()->create(['twofa' => 0]);

        $order = Order::create([
            'order_number' => 'TEST' . uniqid(),
            'user_id' => $customer->id,
            'shipping_address' => ['name' => 'A', 'address_line_1' => 'X', 'city' => 'Mumbai', 'state' => 'MH', 'pincode' => '400001'],
            'billing_address' => ['name' => 'A', 'address_line_1' => 'X', 'city' => 'Mumbai', 'state' => 'MH', 'pincode' => '400001'],
            'subtotal' => $total,
            'discount' => 0,
            'shipping_charge' => 0,
            'tax' => 0,
            'total' => $total,
            'payment_status' => 'paid',
            'order_status' => 'delivered',
        ]);

        return SubOrder::create([
            'sub_order_number' => 'SUB' . uniqid(),
            'order_id' => $order->id,
            'seller_id' => $sellerId,
            'subtotal' => $total,
            'shipping_charge' => 0,
            'tax' => 0,
            'total' => $total,
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    protected function createAdminWithPermissions(array $permissionNames): User
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'twofa' => 0,
        ]);

        foreach ($permissionNames as $permissionName) {
            $permission = Permission::findOrCreate($permissionName, 'web');
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }
}
