<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class CouponController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'coupon',
        'resourceTitle' => 'Coupons',
        'iconPath' => 'M4,4A2,2 0 0,0 2,6V10A2,2 0 0,1 4,12A2,2 0 0,1 2,14V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V14A2,2 0 0,1 20,12A2,2 0 0,1 22,10V6A2,2 0 0,0 20,4H4M4,6H20V8.54C18.76,9.25 18,10.57 18,12C18,13.43 18.76,14.75 20,15.46V18H4V15.46C5.24,14.75 6,13.43 6,12C6,10.57 5.24,9.25 4,8.54V6M8.5,10A1.5,1.5 0 0,0 7,11.5A1.5,1.5 0 0,0 8.5,13A1.5,1.5 0 0,0 10,11.5A1.5,1.5 0 0,0 8.5,10M15.5,10A1.5,1.5 0 0,0 14,11.5A1.5,1.5 0 0,0 15.5,13A1.5,1.5 0 0,0 17,11.5A1.5,1.5 0 0,0 15.5,10Z',
        'actions' => 'cud',
        'actionExpand' => false,
        'fColumn' => 2,
        'formInfo' => [
            'code' => ['label' => 'Coupon Code', 'type' => 'input', 'default' => ''],
            'name' => ['label' => 'Name', 'type' => 'input', 'default' => ''],
            'description' => ['label' => 'Description', 'type' => 'textarea', 'default' => ''],
            'type' => ['label' => 'Discount Type', 'type' => 'select', 'default' => 'percentage', 'options' => [
                ['id' => 'percentage', 'label' => 'Percentage (%)'],
                ['id' => 'fixed', 'label' => 'Fixed Amount (₹)'],
            ]],
            'value' => ['label' => 'Discount Value', 'type' => 'input', 'default' => '0'],
            'max_discount' => ['label' => 'Max Discount (₹)', 'type' => 'input', 'default' => ''],
            'min_order_amount' => ['label' => 'Min Order Amount (₹)', 'type' => 'input', 'default' => '0'],
            'max_uses' => ['label' => 'Max Total Uses', 'type' => 'input', 'default' => ''],
            'per_user_limit' => ['label' => 'Per User Limit', 'type' => 'input', 'default' => '1'],
            'starts_at' => ['label' => 'Start Date', 'type' => 'datetime', 'default' => ''],
            'expires_at' => ['label' => 'Expiry Date', 'type' => 'datetime', 'default' => ''],
            'is_active' => ['label' => 'Active', 'type' => 'switch', 'default' => true],
        ],
    ];

    public function __construct()
    {
        $this->middleware('can:coupon_list', ['only' => ['index']]);
        $this->middleware('can:coupon_create', ['only' => ['create', 'store']]);
        $this->middleware('can:coupon_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:coupon_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $query = Coupon::query();
        
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('code', 'LIKE', "%{$value}%")
                      ->orWhere('name', 'LIKE', "%{$value}%");
            });
        });
        
        $perPage = request()->query('perPage') ?? 15;
        $coupons = QueryBuilder::for($query)
            ->defaultSort('-created_at')
            ->allowedSorts(['code', 'name', 'type', 'value', 'uses_count', 'expires_at', 'is_active', 'created_at'])
            ->allowedFilters(['type', 'is_active', $globalSearch])
            ->paginate($perPage)
            ->withQueryString();
        
        // Add formatted data for display
        $coupons->getCollection()->transform(function ($coupon) {
            $coupon->formatted_value = $coupon->type === 'percentage' 
                ? $coupon->value . '%' 
                : '₹' . number_format($coupon->value, 2);
            $coupon->formatted_expires_at = $coupon->expires_at 
                ? $coupon->expires_at->format('d M Y') 
                : 'Never';
            $coupon->usage_info = $coupon->uses_count . ($coupon->max_uses ? '/' . $coupon->max_uses : '');
            $coupon->is_valid = $coupon->isValid();
            return $coupon;
        });
        
        return Inertia::render('Admin/CouponIndexView', [
            'resourceData' => $coupons,
            'resourceNeo' => $this->resourceNeo,
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('code', 'Code', searchable: true, sortable: true)
                ->column('name', 'Name', searchable: true, sortable: true)
                ->column('type', 'Type', searchable: false, sortable: true)
                ->column('formatted_value', 'Value', searchable: false, sortable: true)
                ->column('usage_info', 'Usage', searchable: false, sortable: true)
                ->column('formatted_expires_at', 'Expires', searchable: false, sortable: true)
                ->column('is_active', 'Status', searchable: false, sortable: true)
                ->column(label: 'Actions')
                ->perPageOptions([15, 30, 50, 100])
                ->selectFilter(key: 'type', label: 'Type', options: [
                    'percentage' => 'Percentage',
                    'fixed' => 'Fixed Amount',
                ])
                ->selectFilter(key: 'is_active', label: 'Status', options: [
                    '1' => 'Active',
                    '0' => 'Inactive',
                ]);
        });
    }

    public function create()
    {
        return Inertia::render('Admin/AddEditView', [
            'formdata' => (object)[
                'type' => ['id' => 'percentage', 'label' => 'Percentage (%)'],
                'is_active' => true,
                'per_user_limit' => 1,
            ],
            'resourceNeo' => $this->resourceNeo,
        ]);
    }

    public function store(Request $request)
    {
        // Handle type if it's sent as an object from Multiselect
        if (is_array($request->type)) {
            $request->merge(['type' => $request->type['id'] ?? null]);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);
        
        // Convert empty strings to null
        $validated['max_discount'] = $validated['max_discount'] ?: null;
        $validated['min_order_amount'] = $validated['min_order_amount'] ?: 0;
        $validated['max_uses'] = $validated['max_uses'] ?: null;
        $validated['per_user_limit'] = $validated['per_user_limit'] ?: 1;
        
        // Uppercase the code
        $validated['code'] = strtoupper($validated['code']);
        
        $coupon = Coupon::create($validated);
        
        \ActivityLog::add([
            'action' => 'created',
            'module' => 'coupon',
            'data_key' => $coupon->code
        ]);
        
        return redirect()->route('coupon.index')->with([
            'message' => 'Coupon created successfully',
            'msg_type' => 'success'
        ]);
    }

    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        
        $formdata = $coupon->toArray();
        
        // Convert type string to object format for Multiselect
        $typeOptions = [
            'percentage' => 'Percentage (%)',
            'fixed' => 'Fixed Amount (₹)',
        ];
        $formdata['type'] = [
            'id' => $coupon->type,
            'label' => $typeOptions[$coupon->type] ?? $coupon->type
        ];
        
        // Format dates for datetime input
        $formdata['starts_at'] = $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : null;
        $formdata['expires_at'] = $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : null;
        
        return Inertia::render('Admin/AddEditView', [
            'formdata' => $formdata,
            'resourceNeo' => $this->resourceNeo,
        ]);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        
        // Handle type if it's sent as an object from Multiselect
        if (is_array($request->type)) {
            $request->merge(['type' => $request->type['id'] ?? null]);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);
        
        // Convert empty strings to null
        $validated['max_discount'] = $validated['max_discount'] ?: null;
        $validated['min_order_amount'] = $validated['min_order_amount'] ?: 0;
        $validated['max_uses'] = $validated['max_uses'] ?: null;
        $validated['per_user_limit'] = $validated['per_user_limit'] ?: 1;
        
        // Uppercase the code
        $validated['code'] = strtoupper($validated['code']);
        
        $coupon->update($validated);
        
        \ActivityLog::add([
            'action' => 'updated',
            'module' => 'coupon',
            'data_key' => $coupon->code
        ]);
        
        return redirect()->route('coupon.index')->with([
            'message' => 'Coupon updated successfully',
            'msg_type' => 'success'
        ]);
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $couponCode = $coupon->code;
        
        $coupon->delete();
        
        \ActivityLog::add([
            'action' => 'deleted',
            'module' => 'coupon',
            'data_key' => $couponCode
        ]);
        
        return redirect()->route('coupon.index')->with([
            'message' => 'Coupon deleted successfully',
            'msg_type' => 'success'
        ]);
    }
}
