<?php

namespace App\Console\Commands;

use App\Models\Seller;
use App\Models\SellerPayoutRequest;
use App\Models\SellerPayoutRequestItem;
use App\Models\SubOrder;
use App\Services\Finance\LedgerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinanceBackfillLedger extends Command
{
    protected $signature = 'finance:backfill-ledger {--chunk=200}';

    protected $description = 'Backfill seller ledger, admin commission entries, and legacy payout requests';

    public function __construct(private readonly LedgerService $ledgerService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');

        $this->info('Backfilling delivered sub-orders into ledger...');
        SubOrder::query()
            ->where('status', 'delivered')
            ->orderBy('id')
            ->chunkById($chunk, function ($subOrders) {
                foreach ($subOrders as $subOrder) {
                    $this->ledgerService->postDeliveredSale($subOrder);
                }
            });

        $this->info('Backfilling legacy paid payouts from sub_orders.payout_at...');
        $groups = SubOrder::query()
            ->whereNotNull('payout_at')
            ->selectRaw('seller_id, DATE(payout_at) as payout_date')
            ->groupBy('seller_id', DB::raw('DATE(payout_at)'))
            ->get();

        foreach ($groups as $group) {
            DB::transaction(function () use ($group) {
                $subOrders = SubOrder::query()
                    ->where('seller_id', $group->seller_id)
                    ->whereDate('payout_at', $group->payout_date)
                    ->where('status', 'delivered')
                    ->get();

                if ($subOrders->isEmpty()) {
                    return;
                }

                $requestNumber = 'LEGACY-' . str_replace('-', '', $group->payout_date) . '-' . $group->seller_id;
                $paymentRef = 'LEGACY-' . $group->payout_date . '-' . $group->seller_id;

                $request = SellerPayoutRequest::query()->firstOrCreate(
                    ['request_number' => $requestNumber],
                    [
                        'seller_id' => $group->seller_id,
                        'requested_amount' => 0,
                        'approved_amount' => 0,
                        'status' => 'paid',
                        'requested_at' => $subOrders->min('payout_at'),
                        'reviewed_at' => $subOrders->max('payout_at'),
                        'paid_at' => $subOrders->max('payout_at'),
                        'payment_reference' => $paymentRef,
                        'payment_method' => 'legacy',
                        'admin_notes' => 'Backfilled from legacy sub_orders.payout_at',
                    ]
                );

                $seller = Seller::query()->where('user_id', $group->seller_id)->first();
                $commissionRate = (float) ($seller?->commission_rate ?? 10);

                $netTotal = 0.0;
                foreach ($subOrders as $subOrder) {
                    $commissionAmount = round((float) $subOrder->total * ($commissionRate / 100), 2);
                    $netAmount = round((float) $subOrder->total - $commissionAmount, 2);

                    SellerPayoutRequestItem::query()->firstOrCreate(
                        ['sub_order_id' => $subOrder->id],
                        [
                            'payout_request_id' => $request->id,
                            'gross_amount' => $subOrder->total,
                            'commission_rate' => $commissionRate,
                            'commission_amount' => $commissionAmount,
                            'net_amount' => $netAmount,
                        ]
                    );

                    $netTotal += $netAmount;
                }

                $netTotal = round($netTotal, 2);

                $request->update([
                    'requested_amount' => $netTotal,
                    'approved_amount' => $netTotal,
                ]);

                $this->ledgerService->createPayoutDebit(
                    sellerUserId: (int) $group->seller_id,
                    payoutRequestId: (int) $request->id,
                    amount: $netTotal,
                    description: 'Legacy payout backfill for ' . $requestNumber,
                    meta: ['legacy' => true, 'payment_reference' => $paymentRef]
                );
            });
        }

        $this->info('Rebuilding per-seller balance snapshots...');
        $sellerIds = SubOrder::query()->distinct()->pluck('seller_id');
        foreach ($sellerIds as $sellerId) {
            $this->ledgerService->rebuildBalanceSnapshot((int) $sellerId);
        }

        $this->info('Backfill complete.');

        return self::SUCCESS;
    }
}
