<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SubOrder;
use App\Models\Seller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class OrderController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'order',
        'resourceTitle' => 'Orders',
        'iconPath' => 'M17,18A2,2 0 0,1 19,20A2,2 0 0,1 17,22C15.89,22 15,21.1 15,20C15,18.89 15.89,18 17,18M1,2H4.27L5.21,4H20A1,1 0 0,1 21,5C21,5.17 20.95,5.34 20.88,5.5L17.3,11.97C16.96,12.58 16.3,13 15.55,13H8.1L7.2,14.63L7.17,14.75A0.25,0.25 0 0,0 7.42,15H19V17H7C5.89,17 5,16.1 5,15C5,14.65 5.09,14.32 5.24,14.04L6.6,11.59L3,4H1V2M7,18A2,2 0 0,1 9,20A2,2 0 0,1 7,22C5.89,22 5,21.1 5,20C5,18.89 5.89,18 7,18M16,11L18.78,6H6.14L8.5,11H16Z',
        'actions' => 'r', // Read only from index, actions handled via custom buttons
        'actionExpand' => false,
        'extraLinks' => [
            [
                'label' => 'View',
                'link' => 'order.show',
                'icon' => 'M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z',
                'color' => 'info',
                'conditions' => [['key' => 'id', 'cond' => '*', 'compvl' => '']]
            ],
        ],
    ];

    public function __construct()
    {
        $this->middleware('can:order_list', ['only' => ['index']]);
        $this->middleware('can:order_view', ['only' => ['show']]);
        $this->middleware('can:order_update', ['only' => ['update']]);
        $this->middleware('can:order_cancel', ['only' => ['cancel']]);
    }

    public function index()
    {
        $query = Order::with(['user', 'subOrders.seller']);
        
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('order_number', 'LIKE', "%{$value}%")
                      ->orWhereHas('user', function ($q) use ($value) {
                          $q->where('name', 'LIKE', "%{$value}%")
                            ->orWhere('email', 'LIKE', "%{$value}%")
                            ->orWhere('phone', 'LIKE', "%{$value}%");
                      });
            });
        });
        
        $perPage = request()->query('perPage') ?? 15;
        $orders = QueryBuilder::for($query)
            ->defaultSort('-created_at')
            ->allowedSorts(['order_number', 'total', 'order_status', 'payment_status', 'created_at'])
            ->allowedFilters(['order_status', 'payment_status', $globalSearch])
            ->paginate($perPage)
            ->withQueryString();
        
        // Add formatted data for display
        $orders->getCollection()->transform(function ($order) {
            $order->customer_name = $order->user->name ?? 'Guest';
            $order->customer_email = $order->user->email ?? $order->user->phone ?? 'N/A';
            $order->items_count = $order->items_count;
            $order->sellers_count = $order->subOrders->count();
            $order->formatted_total = '₹' . number_format($order->total, 2);
            $order->formatted_date = $order->created_at->format('d M Y, h:i A');
            return $order;
        });
        
        return Inertia::render('Admin/OrderIndexView', [
            'resourceData' => $orders,
            'resourceNeo' => $this->resourceNeo,
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('order_number', 'Order #', searchable: true, sortable: true)
                ->column('customer_name', 'Customer', searchable: false, sortable: false)
                ->column('formatted_total', 'Total', searchable: false, sortable: true)
                ->column('sellers_count', 'Sellers', searchable: false, sortable: false)
                ->column('order_status', 'Status', searchable: false, sortable: true)
                ->column('payment_status', 'Payment', searchable: false, sortable: true)
                ->column('formatted_date', 'Date', searchable: false, sortable: true)
                ->column(label: 'Actions')
                ->perPageOptions([15, 30, 50, 100])
                ->selectFilter(key: 'order_status', label: 'Order Status', options: [
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'processing' => 'Processing',
                    'shipped' => 'Shipped',
                    'out_for_delivery' => 'Out for Delivery',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                    'returned' => 'Returned',
                ])
                ->selectFilter(key: 'payment_status', label: 'Payment Status', options: [
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                ]);
        });
    }

    public function show($id)
    {
        $order = Order::with([
            'user',
            'items.product.images',
            'subOrders.seller',
            'subOrders.items.product.images',
            'coupon'
        ])->findOrFail($id);
        
        // Get list of sellers for reference
        $sellers = Seller::select('id', 'business_name', 'user_id')
            ->whereIn('user_id', $order->subOrders->pluck('seller_id'))
            ->get()
            ->keyBy('user_id');
        
        return Inertia::render('Admin/OrderShowView', [
            'order' => $order,
            'sellers' => $sellers,
            'resourceNeo' => $this->resourceNeo,
        ]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $validated = $request->validate([
            'order_status' => 'sometimes|required|in:pending,confirmed,processing,shipped,out_for_delivery,delivered,cancelled,returned',
            'payment_status' => 'sometimes|required|in:pending,paid,failed,refunded',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        // Only allow certain status transitions
        if (isset($validated['order_status'])) {
            $order->order_status = $validated['order_status'];
            
            // Set timestamps based on status
            if ($validated['order_status'] === 'confirmed' && !$order->confirmed_at) {
                $order->confirmed_at = now();
            } elseif ($validated['order_status'] === 'shipped' && !$order->shipped_at) {
                $order->shipped_at = now();
            } elseif ($validated['order_status'] === 'delivered' && !$order->delivered_at) {
                $order->delivered_at = now();
            } elseif ($validated['order_status'] === 'cancelled' && !$order->cancelled_at) {
                $order->cancelled_at = now();
            }
        }
        
        if (isset($validated['payment_status'])) {
            $order->payment_status = $validated['payment_status'];
        }
        
        if (isset($validated['admin_notes'])) {
            $order->notes = $validated['admin_notes'];
        }
        
        $order->save();
        
        \ActivityLog::add([
            'action' => 'updated',
            'module' => 'order',
            'data_key' => $order->order_number
        ]);
        
        return redirect()->back()->with([
            'message' => 'Order updated successfully',
            'msg_type' => 'success'
        ]);
    }

    public function cancel($id)
    {
        $order = Order::with('subOrders.items.product')->findOrFail($id);
        
        if (!$order->canBeCancelled()) {
            return redirect()->back()->with([
                'message' => 'This order cannot be cancelled',
                'msg_type' => 'danger'
            ]);
        }
        
        \DB::transaction(function() use ($order) {
            // Cancel all sub-orders and restore stock
            foreach ($order->subOrders as $subOrder) {
                if ($subOrder->canBeCancelled()) {
                    $subOrder->cancel('Cancelled by admin');
                }
            }
            
            // Update main order status
            $order->update([
                'order_status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        });
        
        \ActivityLog::add([
            'action' => 'cancelled',
            'module' => 'order',
            'data_key' => $order->order_number
        ]);
        
        return redirect()->route('order.index')->with([
            'message' => 'Order cancelled successfully. Stock has been restored.',
            'msg_type' => 'warning'
        ]);
    }
}
