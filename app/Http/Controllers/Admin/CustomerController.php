<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class CustomerController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'customer',
        'resourceTitle' => 'Customers',
        'iconPath' => 'M16,13C15.71,13 15.38,13 15.03,13.05C16.19,13.89 17,15 17,16.5V19H23V16.5C23,14.17 18.33,13 16,13M8,13C5.67,13 1,14.17 1,16.5V19H15V16.5C15,14.17 10.33,13 8,13M8,11A3,3 0 0,0 11,8A3,3 0 0,0 8,5A3,3 0 0,0 5,8A3,3 0 0,0 8,11M16,11A3,3 0 0,0 19,8A3,3 0 0,0 16,5A3,3 0 0,0 13,8A3,3 0 0,0 16,11Z',
        'actions' => 'r', // Read only, no create/delete from admin
        'actionExpand' => false,
        'extraLinks' => [
            [
                'label' => 'View',
                'link' => 'customer.show',
                'icon' => 'M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z',
                'color' => 'info',
                'conditions' => [['key' => 'id', 'cond' => '*', 'compvl' => '']]
            ],
        ],
    ];

    public function __construct()
    {
        $this->middleware('can:customer_list', ['only' => ['index', 'show']]);
        $this->middleware('can:customer_view', ['only' => ['show']]);
        $this->middleware('can:customer_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:customer_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $query = User::where('user_type', 'customer')
            ->withCount('orders')
            ->withSum('orders', 'total');
        
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('name', 'LIKE', "%{$value}%")
                      ->orWhere('email', 'LIKE', "%{$value}%")
                      ->orWhere('phone', 'LIKE', "%{$value}%");
            });
        });
        
        $perPage = request()->query('perPage') ?? 15;
        $customers = QueryBuilder::for($query)
            ->defaultSort('-created_at')
            ->allowedSorts(['name', 'email', 'orders_count', 'orders_sum_total', 'created_at'])
            ->allowedFilters([$globalSearch])
            ->paginate($perPage)
            ->withQueryString();
        
        // Add formatted data for display
        $customers->getCollection()->transform(function ($customer) {
            $customer->total_spent = '₹' . number_format($customer->orders_sum_total ?? 0, 2);
            $customer->formatted_date = $customer->created_at->format('d M Y');
            $customer->is_verified = $customer->email_verified_at || $customer->phone_verified_at;
            return $customer;
        });
        
        return Inertia::render('Admin/CustomerIndexView', [
            'resourceData' => $customers,
            'resourceNeo' => $this->resourceNeo,
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('name', 'Name', searchable: true, sortable: true)
                ->column('email', 'Email', searchable: true, sortable: true)
                ->column('phone', 'Phone', searchable: true, sortable: false)
                ->column('orders_count', 'Orders', searchable: false, sortable: true)
                ->column('total_spent', 'Total Spent', searchable: false, sortable: true)
                ->column('is_verified', 'Verified', searchable: false, sortable: false)
                ->column('formatted_date', 'Joined', searchable: false, sortable: true)
                ->column(label: 'Actions')
                ->perPageOptions([15, 30, 50, 100]);
        });
    }

    public function show($id)
    {
        $customer = User::where('user_type', 'customer')
            ->with(['orders.subOrders', 'addresses', 'reviews.product'])
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->findOrFail($id);
        
        // Get recent orders
        $recentOrders = $customer->orders()
            ->with('subOrders.seller')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return Inertia::render('Admin/CustomerShowView', [
            'customer' => $customer,
            'recentOrders' => $recentOrders,
            'resourceNeo' => $this->resourceNeo,
        ]);
    }

    public function edit($id)
    {
        $customer = User::where('user_type', 'customer')->findOrFail($id);
        
        return Inertia::render('Admin/AddEditView', [
            'formdata' => $customer,
            'resourceNeo' => array_merge($this->resourceNeo, [
                'formInfo' => [
                    'name' => ['label' => 'Name', 'type' => 'input', 'default' => ''],
                    'email' => ['label' => 'Email', 'type' => 'input', 'default' => ''],
                    'phone' => ['label' => 'Phone', 'type' => 'input', 'default' => ''],
                ],
            ]),
        ]);
    }

    public function update(Request $request, $id)
    {
        $customer = User::where('user_type', 'customer')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|unique:users,email,' . $id,
            'phone' => 'sometimes|nullable|string|max:15',
        ]);
        
        $customer->update($validated);
        
        \ActivityLog::add([
            'action' => 'updated',
            'module' => 'customer',
            'data_key' => $customer->name
        ]);
        
        return redirect()->back()->with([
            'message' => 'Customer updated successfully',
            'msg_type' => 'success'
        ]);
    }

    public function destroy($id)
    {
        $customer = User::where('user_type', 'customer')
            ->withCount('orders')
            ->findOrFail($id);
        
        // Prevent deletion if customer has orders
        if ($customer->orders_count > 0) {
            return redirect()->back()->with([
                'message' => 'Cannot delete customer with existing orders',
                'msg_type' => 'danger'
            ]);
        }
        
        $customerName = $customer->name;
        $customer->delete();
        
        \ActivityLog::add([
            'action' => 'deleted',
            'module' => 'customer',
            'data_key' => $customerName
        ]);
        
        return redirect()->route('customer.index')->with([
            'message' => 'Customer deleted successfully',
            'msg_type' => 'success'
        ]);
    }
}
