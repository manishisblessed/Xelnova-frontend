<?php

namespace App\Services\Finance;

use App\Models\AdminCommissionEntry;
use App\Models\Seller;
use App\Models\SellerLedgerEntry;
use App\Models\SubOrder;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    public function postDeliveredSale(SubOrder $subOrder): void
    {
        if ($subOrder->status !== 'delivered') {
            return;
        }

        DB::transaction(function () use ($subOrder) {
            $seller = Seller::query()->where('user_id', $subOrder->seller_id)->first();
            $commissionRate = (float) ($seller?->commission_rate ?? 10.00);
            $baseAmount = (float) $subOrder->total;
            $commissionAmount = round($baseAmount * ($commissionRate / 100), 2);

            $saleKey = 'sale_credit:sub_order:' . $subOrder->id;
            if (!SellerLedgerEntry::query()->where('idempotency_key', $saleKey)->exists()) {
                $this->createEntry(
                    sellerId: (int) $subOrder->seller_id,
                    subOrderId: (int) $subOrder->id,
                    payoutRequestId: null,
                    entryType: 'sale_credit',
                    direction: 'credit',
                    amount: $baseAmount,
                    idempotencyKey: $saleKey,
                    description: 'Sale credited for sub-order ' . $subOrder->sub_order_number,
                    meta: ['sub_order_number' => $subOrder->sub_order_number]
                );
            }

            $commissionKey = 'commission_debit:sub_order:' . $subOrder->id;
            if (!SellerLedgerEntry::query()->where('idempotency_key', $commissionKey)->exists()) {
                $this->createEntry(
                    sellerId: (int) $subOrder->seller_id,
                    subOrderId: (int) $subOrder->id,
                    payoutRequestId: null,
                    entryType: 'commission_debit',
                    direction: 'debit',
                    amount: $commissionAmount,
                    idempotencyKey: $commissionKey,
                    description: 'Commission charged for sub-order ' . $subOrder->sub_order_number,
                    meta: ['commission_rate' => $commissionRate, 'sub_order_number' => $subOrder->sub_order_number]
                );
            }

            AdminCommissionEntry::query()->firstOrCreate(
                ['sub_order_id' => $subOrder->id, 'entry_type' => 'earned'],
                [
                    'seller_id' => $subOrder->seller_id,
                    'commission_rate' => $commissionRate,
                    'base_amount' => $baseAmount,
                    'commission_amount' => $commissionAmount,
                    'notes' => 'Commission earned for delivered sub-order ' . $subOrder->sub_order_number,
                ]
            );
        });
    }

    public function postRefund(SubOrder $subOrder, float $refundAmount): void
    {
        DB::transaction(function () use ($subOrder, $refundAmount) {
            $refundAmount = round(max(0, min((float) $subOrder->total, $refundAmount)), 2);
            if ($refundAmount <= 0) {
                return;
            }

            $seller = Seller::query()->where('user_id', $subOrder->seller_id)->first();
            $commissionRate = (float) ($seller?->commission_rate ?? 10.00);
            $commissionReversal = round($refundAmount * ($commissionRate / 100), 2);

            $refundDebitKey = 'refund_debit:sub_order:' . $subOrder->id . ':amount:' . $refundAmount;
            if (!SellerLedgerEntry::query()->where('idempotency_key', $refundDebitKey)->exists()) {
                $this->createEntry(
                    sellerId: (int) $subOrder->seller_id,
                    subOrderId: (int) $subOrder->id,
                    payoutRequestId: null,
                    entryType: 'refund_debit',
                    direction: 'debit',
                    amount: $refundAmount,
                    idempotencyKey: $refundDebitKey,
                    description: 'Refund debited for sub-order ' . $subOrder->sub_order_number,
                    meta: ['sub_order_number' => $subOrder->sub_order_number]
                );
            }

            $refundCommissionKey = 'refund_commission_credit:sub_order:' . $subOrder->id . ':amount:' . $refundAmount;
            if (!SellerLedgerEntry::query()->where('idempotency_key', $refundCommissionKey)->exists()) {
                $this->createEntry(
                    sellerId: (int) $subOrder->seller_id,
                    subOrderId: (int) $subOrder->id,
                    payoutRequestId: null,
                    entryType: 'refund_commission_credit',
                    direction: 'credit',
                    amount: $commissionReversal,
                    idempotencyKey: $refundCommissionKey,
                    description: 'Refund commission reversal for sub-order ' . $subOrder->sub_order_number,
                    meta: ['commission_rate' => $commissionRate, 'sub_order_number' => $subOrder->sub_order_number]
                );
            }

            AdminCommissionEntry::query()->firstOrCreate(
                ['sub_order_id' => $subOrder->id, 'entry_type' => 'reversed'],
                [
                    'seller_id' => $subOrder->seller_id,
                    'commission_rate' => $commissionRate,
                    'base_amount' => $refundAmount,
                    'commission_amount' => $commissionReversal,
                    'notes' => 'Commission reversed for refunded sub-order ' . $subOrder->sub_order_number,
                ]
            );
        });
    }

    public function calculateAvailableBalance(int $sellerUserId): float
    {
        $credit = (float) SellerLedgerEntry::query()
            ->where('seller_id', $sellerUserId)
            ->where('direction', 'credit')
            ->sum('amount');

        $debit = (float) SellerLedgerEntry::query()
            ->where('seller_id', $sellerUserId)
            ->where('direction', 'debit')
            ->sum('amount');

        return round($credit - $debit, 2);
    }

    public function rebuildBalanceSnapshot(int $sellerUserId): float
    {
        $balance = 0.0;

        $entries = SellerLedgerEntry::query()
            ->where('seller_id', $sellerUserId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        foreach ($entries as $entry) {
            $amount = (float) $entry->amount;
            if ($entry->direction === 'credit') {
                $balance += $amount;
            } else {
                $balance -= $amount;
            }

            $entry->update(['balance_after' => round($balance, 2)]);
        }

        return round($balance, 2);
    }

    public function createPayoutDebit(int $sellerUserId, int $payoutRequestId, float $amount, string $description, array $meta = []): SellerLedgerEntry
    {
        return $this->createEntry(
            sellerId: $sellerUserId,
            subOrderId: null,
            payoutRequestId: $payoutRequestId,
            entryType: 'payout_debit',
            direction: 'debit',
            amount: round($amount, 2),
            idempotencyKey: 'payout_debit:request:' . $payoutRequestId,
            description: $description,
            meta: $meta
        );
    }

    protected function createEntry(
        int $sellerId,
        ?int $subOrderId,
        ?int $payoutRequestId,
        string $entryType,
        string $direction,
        float $amount,
        string $idempotencyKey,
        ?string $description = null,
        ?array $meta = null
    ): SellerLedgerEntry {
        $existing = SellerLedgerEntry::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        $lastBalance = (float) (SellerLedgerEntry::query()
            ->where('seller_id', $sellerId)
            ->orderByDesc('id')
            ->value('balance_after') ?? 0);

        $newBalance = $direction === 'credit'
            ? $lastBalance + $amount
            : $lastBalance - $amount;

        return SellerLedgerEntry::query()->create([
            'seller_id' => $sellerId,
            'sub_order_id' => $subOrderId,
            'payout_request_id' => $payoutRequestId,
            'entry_type' => $entryType,
            'direction' => $direction,
            'amount' => round($amount, 2),
            'balance_after' => round($newBalance, 2),
            'idempotency_key' => $idempotencyKey,
            'description' => $description,
            'meta' => $meta,
        ]);
    }
}
