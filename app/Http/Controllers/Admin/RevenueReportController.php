<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SubOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class RevenueReportController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'revenueReport',
        'resourceTitle' => 'Revenue Report',
        'iconPath' => 'M6,16.5L3,19.44V11H6M11,14.66L9.43,13.32L8,14.64V7H11M16,13L13,16V3H16M18.81,12.81L17,11H22V16L20.21,14.21L13,21.36L9.53,18.34L5.75,22H3L9.47,15.66L13,18.68',
    ];

    public function __construct()
    {
        $this->middleware('can:revenueReport_view', ['only' => ['index']]);
    }

    public function index(Request $request)
    {
        // Default date range: current month
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Revenue breakdown
        $totalRevenue = Order::whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->sum('total');
        
        $totalOrders = Order::whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->count();
        
        // Commission earned (marketplace revenue)
        $commissionEarned = SubOrder::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sum(function($subOrder) {
                $commissionRate = $subOrder->seller->commission_rate ?? 10;
                return $subOrder->total * ($commissionRate / 100);
            });
        
        // Seller payouts
        $sellerPayouts = SubOrder::whereBetween('created_at', [$start, $end])
            ->whereNotNull('payout_at')
            ->sum('total');
        
        // Pending payouts
        $pendingPayouts = SubOrder::whereBetween('created_at', [$start, $end])
            ->where('status', 'delivered')
            ->whereNull('payout_at')
            ->sum('total');
        
        // Revenue by category
        $revenueByCategory = \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.payment_status', 'paid')
            ->select('categories.name as category', \DB::raw('SUM(order_items.total) as revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();
        
        // Refunds
        $totalRefunds = SubOrder::whereBetween('refunded_at', [$start, $end])
            ->where('status', 'refunded')
            ->sum('refund_amount');
        
        // Net revenue (after refunds)
        $netRevenue = $totalRevenue - $totalRefunds;
        
        return Inertia::render('Admin/RevenueReportView', [
            'summary' => [
                'total_revenue' => round($totalRevenue, 2),
                'total_orders' => $totalOrders,
                'commission_earned' => round($commissionEarned, 2),
                'seller_payouts' => round($sellerPayouts, 2),
                'pending_payouts' => round($pendingPayouts, 2),
                'total_refunds' => round($totalRefunds, 2),
                'net_revenue' => round($netRevenue, 2),
            ],
            'revenueByCategory' => $revenueByCategory,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'resourceNeo' => $this->resourceNeo,
        ]);
    }
}
