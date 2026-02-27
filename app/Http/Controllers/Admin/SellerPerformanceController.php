<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SubOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SellerPerformanceController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'sellerPerformance',
        'resourceTitle' => 'Seller Performance',
        'iconPath' => 'M16,11.78L20.24,4.45L21.97,5.45L16.74,14.5L10.23,10.75L5.46,19H22V21H2V3H4V17.54L9.5,8L16,11.78Z',
    ];

    public function __construct()
    {
        $this->middleware('can:sellerPerformance_view', ['only' => ['index']]);
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Get all approved sellers with performance metrics
        $sellers = Seller::where('status', 'approved')
            ->with(['user'])
            ->get()
            ->map(function($seller) use ($start, $end) {
                $subOrders = SubOrder::where('seller_id', $seller->user_id)
                    ->whereBetween('created_at', [$start, $end]);
                
                $totalOrders = $subOrders->count();
                $totalSales = $subOrders->sum('total');
                $deliveredOrders = (clone $subOrders)->where('status', 'delivered')->count();
                $cancelledOrders = (clone $subOrders)->where('status', 'cancelled')->count();
                
                $deliveryRate = $totalOrders > 0 ? ($deliveredOrders / $totalOrders) * 100 : 0;
                $cancellationRate = $totalOrders > 0 ? ($cancelledOrders / $totalOrders) * 100 : 0;
                
                // Average delivery time (in days)
                $avgDeliveryTime = SubOrder::where('seller_id', $seller->user_id)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('status', 'delivered')
                    ->whereNotNull('delivered_at')
                    ->get()
                    ->avg(function($order) {
                        return $order->created_at->diffInDays($order->delivered_at);
                    });
                
                return [
                    'id' => $seller->id,
                    'business_name' => $seller->business_name,
                    'email' => $seller->email,
                    'total_orders' => $totalOrders,
                    'total_sales' => round($totalSales, 2),
                    'delivered_orders' => $deliveredOrders,
                    'cancelled_orders' => $cancelledOrders,
                    'delivery_rate' => round($deliveryRate, 2),
                    'cancellation_rate' => round($cancellationRate, 2),
                    'avg_delivery_days' => round($avgDeliveryTime ?? 0, 1),
                    'formatted_sales' => '₹' . number_format($totalSales, 2),
                ];
            })
            ->sortByDesc('total_sales')
            ->values();
        
        return Inertia::render('Admin/SellerPerformanceView', [
            'sellers' => $sellers,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'resourceNeo' => $this->resourceNeo,
        ]);
    }
}
