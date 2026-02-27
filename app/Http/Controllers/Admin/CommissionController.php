<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminCommissionEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CommissionController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'commission',
        'resourceTitle' => 'Commission Earnings',
        'iconPath' => 'M18.5,2.5C17.84,2.5 17.22,2.71 16.7,3.08L15.46,4L16.5,5.5L17.74,4.58C18.05,4.38 18.42,4.38 18.73,4.58C19.04,4.78 19.23,5.12 19.23,5.5C19.23,5.88 19.04,6.22 18.73,6.42L17.5,7.34L18.53,8.84L19.77,7.92C20.29,7.55 20.66,7 20.89,6.39C21.12,5.78 21.17,5.11 21,4.47C20.83,3.83 20.45,3.26 19.93,2.89C19.41,2.5 18.97,2.5 18.5,2.5M13,3L12,4L15,8L16,7L13,3M5.5,7.5C4.84,7.5 4.22,7.71 3.7,8.08L2.46,9L3.5,10.5L4.74,9.58C5.05,9.38 5.42,9.38 5.73,9.58C6.04,9.78 6.23,10.12 6.23,10.5C6.23,10.88 6.04,11.22 5.73,11.42L4.5,12.34L5.53,13.84L6.77,12.92C7.29,12.55 7.66,12 7.89,11.39C8.12,10.78 8.17,10.11 8,9.47C7.83,8.83 7.45,8.26 6.93,7.89C6.41,7.5 5.97,7.5 5.5,7.5M10,8L9,9L12,13L13,12L10,8M14.5,12.5C13.84,12.5 13.22,12.71 12.7,13.08L11.46,14L12.5,15.5L13.74,14.58C14.05,14.38 14.42,14.38 14.73,14.58C15.04,14.78 15.23,15.12 15.23,15.5C15.23,15.88 15.04,16.22 14.73,16.42L13.5,17.34L14.53,18.84L15.77,17.92C16.29,17.55 16.66,17 16.89,16.39C17.12,15.78 17.17,15.11 17,14.47C16.83,13.83 16.45,13.26 15.93,12.89C15.41,12.5 14.97,12.5 14.5,12.5M3,13L2,14L5,18L6,17L3,13Z',
        'actions' => '',
    ];

    public function __construct()
    {
        $this->middleware('can:commission_view', ['only' => ['index']]);
        $this->middleware('can:commission_edit', ['only' => ['edit', 'update']]);
    }

    public function index()
    {
        $query = AdminCommissionEntry::query()
            ->with(['seller.seller', 'subOrder.order']);

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($subQuery) use ($value) {
                $subQuery->whereHas('subOrder', function ($sq) use ($value) {
                    $sq->where('sub_order_number', 'LIKE', "%{$value}%");
                })->orWhereHas('subOrder.order', function ($oq) use ($value) {
                    $oq->where('order_number', 'LIKE', "%{$value}%");
                })->orWhereHas('seller.seller', function ($s) use ($value) {
                    $s->where('business_name', 'LIKE', "%{$value}%");
                });
            });
        });

        $perPage = request()->query('perPage') ?? 15;
        $resourceData = QueryBuilder::for($query)
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at', 'commission_amount', 'base_amount', 'entry_type'])
            ->allowedFilters(['entry_type', $globalSearch])
            ->paginate($perPage)
            ->withQueryString();

        $resourceData->getCollection()->transform(function ($entry) {
            $entry->seller_name = $entry->seller?->seller?->business_name ?? $entry->seller?->name ?? 'Unknown';
            $entry->sub_order_number = $entry->subOrder?->sub_order_number;
            $entry->order_number = $entry->subOrder?->order?->order_number;
            return $entry;
        });

        $summaryQuery = AdminCommissionEntry::query();
        if ($status = request()->query('filter.entry_type')) {
            $summaryQuery->where('entry_type', $status);
        }

        $totalEarned = (float) (clone $summaryQuery)->where('entry_type', 'earned')->sum('commission_amount');
        $totalReversed = (float) (clone $summaryQuery)->where('entry_type', 'reversed')->sum('commission_amount');

        return Inertia::render('Admin/CommissionIndexView', [
            'resourceData' => $resourceData,
            'resourceNeo' => $this->resourceNeo,
            'summary' => [
                'total_earned' => round($totalEarned, 2),
                'total_reversed' => round($totalReversed, 2),
                'net_commission' => round($totalEarned - $totalReversed, 2),
            ],
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('seller_name', 'Seller', searchable: false, sortable: false)
                ->column('sub_order_number', 'Sub-Order', searchable: false, sortable: false)
                ->column('order_number', 'Order #', searchable: false, sortable: false)
                ->column('entry_type', 'Type', searchable: false, sortable: true)
                ->column('commission_rate', 'Rate %', searchable: false, sortable: false)
                ->column('base_amount', 'Base Amount', searchable: false, sortable: true)
                ->column('commission_amount', 'Commission', searchable: false, sortable: true)
                ->column('created_at', 'Date', searchable: false, sortable: true)
                ->perPageOptions([15, 30, 50, 100])
                ->selectFilter(key: 'entry_type', label: 'Type', options: [
                    'earned' => 'Earned',
                    'reversed' => 'Reversed',
                ]);
        });
    }

    public function edit()
    {
        return redirect()->route('commission.index')->with([
            'message' => 'Commission configuration is managed per seller profile. This page is report-only.',
            'msg_type' => 'info',
        ]);
    }

    public function update(Request $request)
    {
        return redirect()->route('commission.index')->with([
            'message' => 'Commission configuration is managed per seller profile. This page is report-only.',
            'msg_type' => 'info',
        ]);
    }
}
