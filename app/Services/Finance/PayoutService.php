<?php

namespace App\Services\Finance;

use App\Models\Seller;
use App\Models\SellerPayoutRequest;
use App\Models\SellerPayoutRequestItem;
use App\Models\SubOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayoutService
{
    public function __construct(private readonly LedgerService $ledgerService)
    {
    }

    public function createRequestForFullBalance(int $sellerUserId, ?string $notes = null): SellerPayoutRequest
    {
        return DB::transaction(function () use ($sellerUserId, $notes) {
            if (SellerPayoutRequest::query()->where('seller_id', $sellerUserId)->where('status', 'pending')->exists()) {
                throw ValidationException::withMessages([
                    'payout_request' => 'A pending payout request already exists.',
                ]);
            }

            $seller = Seller::query()->with(['bankAccounts' => fn($q) => $q->where('verification_status', 'verified')])
                ->where('user_id', $sellerUserId)
                ->first();

            if (!$seller || $seller->bankAccounts->isEmpty()) {
                throw ValidationException::withMessages([
                    'bank_account' => 'A verified bank account is required before requesting payout.',
                ]);
            }

            $eligibleSubOrders = $this->eligibleSubOrdersQuery($sellerUserId)->lockForUpdate()->get();
            if ($eligibleSubOrders->isEmpty()) {
                throw ValidationException::withMessages([
                    'payout_request' => 'No eligible delivered orders available for payout.',
                ]);
            }

            $items = $eligibleSubOrders->map(function (SubOrder $subOrder) use ($seller) {
                $commissionRate = (float) ($seller->commission_rate ?? 10);
                $gross = (float) $subOrder->total;
                $commission = round($gross * ($commissionRate / 100), 2);

                return [
                    'sub_order' => $subOrder,
                    'commission_rate' => $commissionRate,
                    'gross_amount' => round($gross, 2),
                    'commission_amount' => $commission,
                    'net_amount' => round($gross - $commission, 2),
                ];
            });

            $requestAmount = round((float) $items->sum('net_amount'), 2);
            if ($requestAmount < 500) {
                throw ValidationException::withMessages([
                    'payout_request' => 'Minimum payout request amount is ₹500.00.',
                ]);
            }

            $availableBalance = $this->ledgerService->calculateAvailableBalance($sellerUserId);
            if ($availableBalance < $requestAmount) {
                throw ValidationException::withMessages([
                    'payout_request' => 'Ledger balance is lower than eligible payout amount. Please contact admin.',
                ]);
            }

            $request = SellerPayoutRequest::query()->create([
                'request_number' => $this->generateRequestNumber(),
                'seller_id' => $sellerUserId,
                'requested_amount' => $requestAmount,
                'status' => 'pending',
                'requested_at' => now(),
                'seller_notes' => $notes,
            ]);

            foreach ($items as $item) {
                SellerPayoutRequestItem::query()->create([
                    'payout_request_id' => $request->id,
                    'sub_order_id' => $item['sub_order']->id,
                    'gross_amount' => $item['gross_amount'],
                    'commission_rate' => $item['commission_rate'],
                    'commission_amount' => $item['commission_amount'],
                    'net_amount' => $item['net_amount'],
                ]);
            }

            return $request->load(['items.subOrder.order']);
        });
    }

    public function approveRequest(int $requestId, int $adminId, ?string $notes = null): SellerPayoutRequest
    {
        return DB::transaction(function () use ($requestId, $adminId, $notes) {
            $request = SellerPayoutRequest::query()->lockForUpdate()->findOrFail($requestId);
            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only pending requests can be approved.']);
            }

            $request->update([
                'status' => 'approved',
                'approved_amount' => $request->requested_amount,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'admin_notes' => $notes,
            ]);

            return $request;
        });
    }

    public function rejectRequest(int $requestId, int $adminId, string $reason): SellerPayoutRequest
    {
        return DB::transaction(function () use ($requestId, $adminId, $reason) {
            $request = SellerPayoutRequest::query()->lockForUpdate()->findOrFail($requestId);
            if ($request->status !== 'pending' && $request->status !== 'approved') {
                throw ValidationException::withMessages(['status' => 'Only pending or approved requests can be rejected.']);
            }

            $request->update([
                'status' => 'rejected',
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'admin_notes' => $reason,
            ]);

            return $request;
        });
    }

    public function markPaid(int $requestId, int $adminId, string $reference, ?string $method = null, ?string $notes = null): SellerPayoutRequest
    {
        return DB::transaction(function () use ($requestId, $adminId, $reference, $method, $notes) {
            $request = SellerPayoutRequest::query()->with(['items'])->lockForUpdate()->findOrFail($requestId);
            if (!in_array($request->status, ['approved', 'pending'], true)) {
                throw ValidationException::withMessages(['status' => 'Only pending/approved requests can be marked as paid.']);
            }

            if ($request->items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Payout request has no items.']);
            }

            $approvedAmount = (float) ($request->approved_amount ?? $request->requested_amount);

            $this->ledgerService->createPayoutDebit(
                sellerUserId: (int) $request->seller_id,
                payoutRequestId: (int) $request->id,
                amount: $approvedAmount,
                description: 'Payout processed for request ' . $request->request_number,
                meta: [
                    'payment_reference' => $reference,
                    'payment_method' => $method,
                ]
            );

            $subOrderIds = $request->items->pluck('sub_order_id')->all();
            if (!empty($subOrderIds)) {
                SubOrder::query()->whereIn('id', $subOrderIds)->update([
                    'payout_at' => now(),
                    'admin_notes' => $notes,
                ]);
            }

            $request->update([
                'status' => 'paid',
                'approved_amount' => $approvedAmount,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'paid_at' => now(),
                'payment_reference' => $reference,
                'payment_method' => $method,
                'admin_notes' => $notes,
            ]);

            return $request;
        });
    }

    public function eligibleSubOrdersQuery(int $sellerUserId)
    {
        return SubOrder::query()
            ->where('seller_id', $sellerUserId)
            ->where('status', 'delivered')
            ->whereNull('refunded_at')
            ->whereDoesntHave('payoutRequestItem');
    }

    protected function generateRequestNumber(): string
    {
        do {
            $number = 'PR' . now()->format('ymdHis') . random_int(100, 999);
        } while (SellerPayoutRequest::query()->where('request_number', $number)->exists());

        return $number;
    }
}
