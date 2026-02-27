<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubOrder;
use App\Services\Finance\LedgerService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class RefundController extends Controller
{
    public function __construct(private readonly LedgerService $ledgerService)
    {
        $this->middleware('can:refund_list', ['only' => ['index']]);
        $this->middleware('can:refund_approve', ['only' => ['approve']]);
        $this->middleware('can:refund_reject', ['only' => ['reject']]);
    }

    protected $resourceNeo = [
        'resourceName' => 'refund',
        'resourceTitle' => 'Refund Requests',
        'iconPath' => 'M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M11,16.5L6.5,12L7.91,10.59L11,13.67L16.59,8.09L18,9.5L11,16.5Z',
        'actions' => 'r',
        'actionExpand' => false,
    ];

    public function index()
    {
        // Get sub-orders with refund requests
        $query = SubOrder::with(['order.user', 'seller', 'items.product'])
            ->whereIn('status', ['refund_requested', 'refunded']);
        
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('sub_order_number', 'LIKE', "%{$value}%")
                      ->orWhereHas('order', function($q) use ($value) {
                          $q->where('order_number', 'LIKE', "%{$value}%");
                      });
            });
        });
        
        $perPage = request()->query('perPage') ?? 15;
        $refunds = QueryBuilder::for($query)
            ->defaultSort('-updated_at')
            ->allowedSorts(['sub_order_number', 'total', 'status', 'updated_at'])
            ->allowedFilters(['status', $globalSearch])
            ->paginate($perPage)
            ->withQueryString();
        
        // Add formatted data
        $refunds->getCollection()->transform(function ($refund) {
            $refund->customer_name = $refund->order->user->name ?? 'Guest';
            $refund->seller_name = $refund->seller?->seller?->business_name ?? $refund->seller?->name ?? 'Unknown';
            $refund->formatted_amount = '₹' . number_format($refund->refund_amount ?? $refund->total, 2);
            $refund->formatted_date = $refund->updated_at->format('d M Y, h:i A');
            return $refund;
        });
        
        return Inertia::render('Admin/RefundIndexView', [
            'resourceData' => $refunds,
            'resourceNeo' => $this->resourceNeo,
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('sub_order_number', 'Sub-Order #', searchable: true, sortable: true)
                ->column('customer_name', 'Customer', searchable: false, sortable: false)
                ->column('seller_name', 'Seller', searchable: false, sortable: false)
                ->column('formatted_amount', 'Amount', searchable: false, sortable: true)
                ->column('refund_reason', 'Reason', searchable: false, sortable: false)
                ->column('status', 'Status', searchable: false, sortable: true)
                ->column('formatted_date', 'Date', searchable: false, sortable: true)
                ->column(label: 'Actions')
                ->perPageOptions([15, 30, 50, 100])
                ->selectFilter(key: 'status', label: 'Status', options: [
                    'refund_requested' => 'Pending',
                    'refunded' => 'Refunded',
                ]);
        });
    }

    public function show($id)
    {
        $refund = SubOrder::with(['order.user', 'seller', 'items.product.images'])
            ->findOrFail($id);
        
        return Inertia::render('Admin/RefundShowView', [
            'refund' => $refund,
            'resourceNeo' => $this->resourceNeo,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $subOrder = SubOrder::findOrFail($id);
        
        if ($subOrder->status !== 'refund_requested') {
            return redirect()->back()->with([
                'message' => 'This refund request has already been processed',
                'msg_type' => 'danger'
            ]);
        }
        
        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0|max:' . $subOrder->total,
            'admin_notes' => 'nullable|string|max:500',
        ]);
        
        \DB::transaction(function() use ($subOrder, $validated) {
            // Update sub-order
            $subOrder->update([
                'status' => 'refunded',
                'refund_amount' => $validated['refund_amount'],
                'refunded_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);
            
            // Update order items
            $subOrder->items()->update(['status' => 'refunded']);
            
            // TODO: Process actual refund via payment gateway
            // TODO: Restore stock if needed
            $this->ledgerService->postRefund($subOrder->fresh(), (float) $validated['refund_amount']);
        });
        
        \ActivityLog::add([
            'action' => 'approved_refund',
            'module' => 'refund',
            'data_key' => $subOrder->sub_order_number
        ]);
        
        return redirect()->route('refund.index')->with([
            'message' => 'Refund approved successfully',
            'msg_type' => 'success'
        ]);
    }

    public function reject(Request $request, $id)
    {
        $subOrder = SubOrder::findOrFail($id);
        
        if ($subOrder->status !== 'refund_requested') {
            return redirect()->back()->with([
                'message' => 'This refund request has already been processed',
                'msg_type' => 'danger'
            ]);
        }
        
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        $subOrder->update([
            'status' => 'delivered', // Revert to delivered
            'admin_notes' => 'Refund rejected: ' . $validated['rejection_reason'],
        ]);
        
        \ActivityLog::add([
            'action' => 'rejected_refund',
            'module' => 'refund',
            'data_key' => $subOrder->sub_order_number
        ]);
        
        return redirect()->route('refund.index')->with([
            'message' => 'Refund request rejected',
            'msg_type' => 'warning'
        ]);
    }
}
