<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SubOrder;
use App\Models\Seller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SalesReportController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'salesReport',
        'resourceTitle' => 'Sales Report',
        'iconPath' => 'M22,21H2V3H4V19H6V10H10V19H12V6H16V19H18V14H22V21Z',
    ];

    public function __construct()
    {
        $this->middleware('can:salesReport_view', ['only' => ['index']]);
        $this->middleware('can:salesReport_export', ['only' => ['export']]);
    }

    public function index(Request $request)
    {
        // Default date range: last 30 days
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $groupBy = $request->input('group_by', 'day'); // day, week, month
        
        // Parse dates
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Overall statistics
        $overallStats = [
            'total_orders' => Order::whereBetween('created_at', [$start, $end])->count(),
            'total_revenue' => Order::whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'paid')
                ->sum('total'),
            'avg_order_value' => Order::whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'paid')
                ->avg('total') ?? 0,
            'completed_orders' => Order::whereBetween('created_at', [$start, $end])
                ->where('order_status', 'delivered')
                ->count(),
            'cancelled_orders' => Order::whereBetween('created_at', [$start, $end])
                ->where('order_status', 'cancelled')
                ->count(),
        ];
        
        // Sales over time (for chart)
        $salesData = $this->getSalesDataByPeriod($start, $end, $groupBy);
        
        // Top sellers
        $topSellers = SubOrder::select('seller_id')
            ->selectRaw('SUM(total) as total_sales')
            ->selectRaw('COUNT(*) as order_count')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->groupBy('seller_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->with('seller:id,user_id,business_name')
            ->get();
        
        // Order status breakdown
        $statusBreakdown = Order::selectRaw('order_status, COUNT(*) as count')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('order_status')
            ->get()
            ->pluck('count', 'order_status');
        
        // Payment method breakdown
        $paymentBreakdown = Order::selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->groupBy('payment_method')
            ->get();
        
        return Inertia::render('Admin/SalesReportView', [
            'overallStats' => $overallStats,
            'salesData' => $salesData,
            'topSellers' => $topSellers,
            'statusBreakdown' => $statusBreakdown,
            'paymentBreakdown' => $paymentBreakdown,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_by' => $groupBy,
            ],
            'resourceNeo' => $this->resourceNeo,
        ]);
    }

    private function getSalesDataByPeriod($start, $end, $groupBy)
    {
        $dateFormat = match($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };
        
        return Order::selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as period")
            ->selectRaw('SUM(total) as revenue')
            ->selectRaw('COUNT(*) as orders')
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    public function export(Request $request)
    {
        // TODO: Implement CSV/Excel export
        return redirect()->back()->with([
            'message' => 'Export feature coming soon',
            'msg_type' => 'info'
        ]);
    }
}
