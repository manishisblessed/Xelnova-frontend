<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerPayoutRequest;
use App\Services\Finance\PayoutService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PayoutController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'payout',
        'resourceTitle' => 'Seller Payout Requests',
        'iconPath' => 'M3,6H21V18H3V6M12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9M7,8A2,2 0 0,1 5,10V14A2,2 0 0,1 7,16H17A2,2 0 0,1 19,14V10A2,2 0 0,1 17,8H7Z',
        'actions' => 'r',
        'actionExpand' => false,
    ];

    public function __construct(private readonly PayoutService $payoutService)
    {
        $this->middleware('can:payout_list', ['only' => ['index']]);
        $this->middleware('can:payout_view', ['only' => ['show']]);
        $this->middleware('can:payout_process', ['only' => ['approve', 'reject', 'process']]);
    }

    public function index()
    {
        $query = SellerPayoutRequest::query()
            ->with(['seller.seller', 'items'])
            ->withCount('items');

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($subQuery) use ($value) {
                $subQuery->where('request_number', 'LIKE', "%{$value}%")
                    ->orWhere('payment_reference', 'LIKE', "%{$value}%")
                    ->orWhereHas('seller.seller', function ($sq) use ($value) {
                        $sq->where('business_name', 'LIKE', "%{$value}%");
                    });
            });
        });

        $perPage = request()->query('perPage') ?? 15;
        $resourceData = QueryBuilder::for($query)
            ->defaultSort('-requested_at')
            ->allowedSorts(['requested_at', 'requested_amount', 'status', 'paid_at'])
            ->allowedFilters(['status', $globalSearch])
            ->paginate($perPage)
            ->withQueryString();

        $resourceData->getCollection()->transform(function ($request) {
            $request->seller_name = $request->seller?->seller?->business_name ?? $request->seller?->name ?? 'Unknown';
            return $request;
        });

        return Inertia::render('Admin/PayoutIndexView', [
            'resourceData' => $resourceData,
            'resourceNeo' => $this->resourceNeo,
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('request_number', 'Request #', searchable: false, sortable: true)
                ->column('seller_name', 'Seller', searchable: false, sortable: false)
                ->column('items_count', 'Orders', searchable: false, sortable: false)
                ->column('requested_amount', 'Requested', searchable: false, sortable: true)
                ->column('approved_amount', 'Approved', searchable: false, sortable: true)
                ->column('status', 'Status', searchable: false, sortable: true)
                ->column('requested_at', 'Requested At', searchable: false, sortable: true)
                ->column(label: 'Actions')
                ->perPageOptions([15, 30, 50, 100])
                ->selectFilter(key: 'status', label: 'Status', options: [
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'paid' => 'Paid',
                ]);
        });
    }

    public function show($id)
    {
        $request = SellerPayoutRequest::query()
            ->with([
                'seller.seller.bankAccounts',
                'reviewer',
                'items.subOrder.order',
            ])
            ->findOrFail($id);

        $sellerProfile = $request->seller?->seller;
        $verifiedBankAccounts = $sellerProfile?->bankAccounts
            ? $sellerProfile->bankAccounts->where('verification_status', 'verified')->values()
            : collect();

        $summary = [
            'gross_total' => round((float) $request->items->sum('gross_amount'), 2),
            'commission_total' => round((float) $request->items->sum('commission_amount'), 2),
            'net_total' => round((float) $request->items->sum('net_amount'), 2),
            'item_count' => $request->items->count(),
        ];

        return Inertia::render('Admin/PayoutShowView', [
            'payoutRequest' => $request,
            'items' => $request->items,
            'verifiedBankAccounts' => $verifiedBankAccounts,
            'summary' => $summary,
            'resourceNeo' => $this->resourceNeo,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $payoutRequest = $this->payoutService->approveRequest((int) $id, (int) auth()->id(), $validated['notes'] ?? null);

        \ActivityLog::add([
            'action' => 'approved_payout_request',
            'module' => 'payout',
            'data_key' => $payoutRequest->request_number,
        ]);

        return redirect()->route('payout.show', $id)->with([
            'message' => 'Payout request approved.',
            'msg_type' => 'success',
        ]);
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $payoutRequest = $this->payoutService->rejectRequest((int) $id, (int) auth()->id(), $validated['reason']);

        \ActivityLog::add([
            'action' => 'rejected_payout_request',
            'module' => 'payout',
            'data_key' => $payoutRequest->request_number,
        ]);

        return redirect()->route('payout.show', $id)->with([
            'message' => 'Payout request rejected.',
            'msg_type' => 'warning',
        ]);
    }

    public function process(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_reference' => 'required|string|max:255',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $payoutRequest = $this->payoutService->markPaid(
            (int) $id,
            (int) auth()->id(),
            $validated['payment_reference'],
            $validated['payment_method'] ?? null,
            $validated['notes'] ?? null
        );

        \ActivityLog::add([
            'action' => 'processed_payout',
            'module' => 'payout',
            'data_key' => $payoutRequest->request_number . ' - ' . $validated['payment_reference'],
        ]);

        return redirect()->route('payout.show', $id)->with([
            'message' => 'Payout marked as paid successfully.',
            'msg_type' => 'success',
        ]);
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
    }

    public function edit($id)
    {
        abort(404);
    }

    public function update(Request $request, $id)
    {
        abort(404);
    }

    public function destroy($id)
    {
        abort(404);
    }
}
