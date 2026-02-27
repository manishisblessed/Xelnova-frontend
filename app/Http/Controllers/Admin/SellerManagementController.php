<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class SellerManagementController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'seller',
        'resourceTitle' => 'Sellers',
        'iconPath' => 'M12,18H6V14H12M21,14V12L20,7H4L3,12V14H4V20H14V14H18V20H20V14M20,4H4V6H20V4Z',
        'actions' => 'cud',
        'actionExpand' => false,
        'fColumn' => 2,
        'formInfo' => [
            'business_name' => ['label' => 'Business Name', 'type' => 'input', 'default' => ''],
            'business_type' => ['label' => 'Business Type', 'type' => 'select', 'default' => 'individual', 'options' => [
                ['id' => 'individual', 'label' => 'Individual'],
                ['id' => 'company', 'label' => 'Company'],
                ['id' => 'partnership', 'label' => 'Partnership'],
            ]],
            'business_registration_number' => ['label' => 'Registration Number', 'type' => 'input', 'default' => ''],
            'business_address' => ['label' => 'Business Address', 'type' => 'textarea', 'default' => ''],
            'city' => ['label' => 'City', 'type' => 'input', 'default' => ''],
            'state' => ['label' => 'State', 'type' => 'input', 'default' => ''],
            'postal_code' => ['label' => 'Postal Code', 'type' => 'input', 'default' => ''],
            'country' => ['label' => 'Country', 'type' => 'input', 'default' => 'India'],
            'phone' => ['label' => 'Phone', 'type' => 'input', 'default' => ''],
            'email' => ['label' => 'Email', 'type' => 'input', 'default' => ''],
            'gst_number' => ['label' => 'GST Number', 'type' => 'input', 'default' => ''],
            'pan_number' => ['label' => 'PAN Number', 'type' => 'input', 'default' => ''],
            'commission_rate' => ['label' => 'Commission Rate (%)', 'type' => 'input', 'default' => '10.00'],
            'user_name' => ['label' => 'User Name', 'type' => 'input', 'default' => ''],
            'user_email' => ['label' => 'User Email', 'type' => 'input', 'default' => ''],
            'user_password' => ['label' => 'Password', 'type' => 'password', 'default' => ''],
            'user_password_confirmation' => ['label' => 'Confirm Password', 'type' => 'password', 'default' => ''],
        ],
        'extraLinks' => [
            [
                'label' => 'Overview',
                'link' => 'seller.overview',
                'icon' => 'M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z',
                'color' => 'info',
                'conditions' => [['key' => 'id', 'cond' => '*', 'compvl' => '']]
            ],
            [
                'label' => 'Documents',
                'link' => 'sellerDocument.index',
                'icon' => 'M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z',
                'color' => 'info',
                'conditions' => [['key' => 'id', 'cond' => '*', 'compvl' => '']]
            ],
            [
                'label' => 'Banks',
                'link' => 'sellerBank.index',
                'icon' => 'M11.5,1L2,6V8H21V6M16,10V17H19V10M2,22H21V19H2M10,10V17H13V10M4,10V17H7V10H4Z',
                'color' => 'info',
                'conditions' => [['key' => 'id', 'cond' => '*', 'compvl' => '']]
            ],
        ],
        'extraMainLinks' => [],
    ];

    public function __construct()
    {
        $this->middleware('can:seller_list', ['only' => ['index', 'show']]);
        $this->middleware('can:seller_view', ['only' => ['show']]);
        $this->middleware('can:seller_create', ['only' => ['create', 'store']]);
        $this->middleware('can:seller_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:seller_approve', ['only' => ['approve']]);
        $this->middleware('can:seller_suspend', ['only' => ['suspend']]);
        $this->middleware('can:seller_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $query = Seller::with(['user.roles', 'approvedBy']);
        
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('business_name', 'LIKE', "%{$value}%")
                      ->orWhere('email', 'LIKE', "%{$value}%")
                      ->orWhere('phone', 'LIKE', "%{$value}%")
                      ->orWhere('gst_number', 'LIKE', "%{$value}%");
            });
        });
        
        $perPage = request()->query('perPage') ?? 10;
        $sellers = QueryBuilder::for($query)
            ->defaultSort('-created_at')
            ->allowedSorts(['business_name', 'status', 'created_at', 'approved_at'])
            ->allowedFilters(['status', 'verification_status', 'business_type', $globalSearch])
            ->paginate($perPage)
            ->withQueryString();
        
        return Inertia::render('Admin/SellerIndexView', [
            'resourceData' => $sellers,
            'resourceNeo' => $this->resourceNeo,
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('business_name', 'Business Name', searchable: true, sortable: true)
                ->column('email', 'Email', searchable: true, sortable: true)
                ->column('phone', 'Phone', searchable: false, sortable: false)
                ->column('status', 'Status', searchable: false, sortable: true)
                ->column('verification_status', 'Verification', searchable: false, sortable: false)
                ->column('created_at', 'Created Date', searchable: false, sortable: true)
                ->column(label: 'Actions')
                ->perPageOptions([10, 15, 30, 50, 100])
                ->selectFilter(key: 'status', label: 'Status', options: [
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'suspended' => 'Suspended',
                    'rejected' => 'Rejected',
                ])
                ->selectFilter(key: 'verification_status', label: 'Verification', options: [
                    'unverified' => 'Unverified',
                    'verified' => 'Verified',
                    'rejected' => 'Rejected',
                ])
                ->selectFilter(key: 'business_type', label: 'Business Type', options: [
                    'individual' => 'Individual',
                    'company' => 'Company',
                    'partnership' => 'Partnership',
                ]);
        });
    }

    public function show($id)
    {
        // Redirect to overview page instead
        return redirect()->route('seller.overview', $id);
    }

    public function create()
    {
        return Inertia::render('Admin/AddEditView', [
            'formdata' => (object)[], // Empty object for create mode
            'resourceNeo' => $this->resourceNeo
        ]);
    }

    public function edit($id)
    {
        $seller = Seller::with(['user.roles'])->findOrFail($id);
        
        // Merge seller data with user data for form
        $formdata = $seller->toArray();
        $formdata['user_name'] = $seller->user->name ?? '';
        $formdata['user_email'] = $seller->user->email ?? '';
        
        // Convert business_type string to object format for Multiselect
        if (isset($formdata['business_type'])) {
            $businessTypeOptions = [
                'individual' => 'Individual',
                'company' => 'Company',
                'partnership' => 'Partnership',
            ];
            $formdata['business_type'] = [
                'id' => $formdata['business_type'],
                'label' => $businessTypeOptions[$formdata['business_type']] ?? $formdata['business_type']
            ];
        }
        
        return Inertia::render('Admin/AddEditView', [
            'formdata' => $formdata,
            'resourceNeo' => $this->resourceNeo
        ]);
    }

    public function store(Request $request)
    {
        // Handle business_type if it's sent as an object from Multiselect
        if (is_array($request->business_type)) {
            $request->merge(['business_type' => $request->business_type['id'] ?? null]);
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|in:individual,company,partnership',
            'business_registration_number' => 'nullable|string|max:255',
            'business_address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'country' => 'nullable|string|max:100',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|unique:sellers,email',
            'gst_number' => 'nullable|string|size:15',
            'pan_number' => 'nullable|string|size:10',
            'commission_rate' => 'required|numeric|min:0|max:100',
            
            // User account details
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email',
            'user_password' => 'required|string|min:8|confirmed',
        ]);
        
        // Create user account first
        $user = \App\Models\User::create([
            'name' => $validated['user_name'],
            'email' => $validated['user_email'],
            'password' => bcrypt($validated['user_password']),
            'user_type' => 'seller',
        ]);
        
        // Assign seller role if exists
        // $user->assignRole('seller');
        
        // Create seller
        $seller = Seller::create([
            'user_id' => $user->id,
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'],
            'business_registration_number' => $validated['business_registration_number'] ?? null,
            'business_address' => $validated['business_address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'] ?? 'India',
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'gst_number' => $validated['gst_number'] ?? null,
            'pan_number' => $validated['pan_number'] ?? null,
            'commission_rate' => $validated['commission_rate'],
            'status' => 'pending', // Default status
        ]);
        
        \ActivityLog::add([
            'action' => 'created',
            'module' => 'seller',
            'data_key' => $seller->business_name
        ]);
        
        return redirect()->route('seller.index')->with([
            'message' => 'Seller created successfully',
            'msg_type' => 'success'
        ]);
    }

    public function update(Request $request, $id)
    {
        $seller = Seller::with('user')->findOrFail($id);
        
        // Handle business_type if it's sent as an object from Multiselect
        if (is_array($request->business_type)) {
            $request->merge(['business_type' => $request->business_type['id'] ?? null]);
        }

        $validationRules = [
            'commission_rate' => 'required|numeric|min:0|max:100',
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|in:individual,company,partnership',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|unique:sellers,email,' . $id,
            'gst_number' => 'nullable|string|size:15',
            'pan_number' => 'nullable|string|size:10',
        ];

        // Add password validation only if password is provided
        if ($request->filled('user_password')) {
            $validationRules['user_password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($validationRules);
        
        // Update seller data
        $seller->update($validated);
        
        // Update user password if provided
        if ($request->filled('user_password') && $seller->user) {
            $seller->user->update([
                'password' => $request->user_password
            ]);
        }
        
        \ActivityLog::add([
            'action' => 'updated',
            'module' => 'seller',
            'data_key' => $seller->business_name
        ]);
        
        return redirect()->route('seller.index')->with([
            'message' => 'Seller updated successfully',
            'msg_type' => 'success'
        ]);
    }


    public function approve(Request $request, $id)
    {
        $seller = Seller::findOrFail($id);
        
        if ($seller->status === 'approved') {
            return redirect()->route('seller.index')->with([
                'message' => 'Seller is already approved',
                'msg_type' => 'info'
            ]);
        }
        
        $seller->update([
            'status' => 'approved',
            'verification_status' => 'verified',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejection_reason' => null,
        ]);
        
        \ActivityLog::add([
            'action' => 'approved',
            'module' => 'seller',
            'data_key' => $seller->business_name
        ]);
        
        // TODO: Send approval email to seller
        
        return redirect()->route('seller.index')->with([
            'message' => 'Seller approved successfully',
            'msg_type' => 'success'
        ]);
    }

    public function suspend(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        $seller = Seller::findOrFail($id);
        
        if ($seller->status === 'suspended') {
            return redirect()->route('seller.index')->with([
                'message' => 'Seller is already suspended',
                'msg_type' => 'info'
            ]);
        }
        
        $seller->update([
            'status' => 'suspended',
            'rejection_reason' => $request->reason,
        ]);
        
        \ActivityLog::add([
            'action' => 'suspended',
            'module' => 'seller',
            'data_key' => $seller->business_name
        ]);
        
        // TODO: Send suspension email to seller
        
        return redirect()->route('seller.index')->with([
            'message' => 'Seller suspended successfully',
            'msg_type' => 'warning'
        ]);
    }

    public function destroy($id)
    {
        $seller = Seller::findOrFail($id);
        $businessName = $seller->business_name;
        
        $seller->delete();
        
        \ActivityLog::add([
            'action' => 'deleted',
            'module' => 'seller',
            'data_key' => $businessName
        ]);
        
        return redirect()->route('seller.index')->with([
            'message' => 'Seller deleted successfully',
            'msg_type' => 'success'
        ]);
    }

    public function overview($id)
    {
        $seller = Seller::with(['user.roles', 'documents', 'bankAccounts', 'approvedBy'])
            ->findOrFail($id);
        
        // Count documents by verification status
        $documentsStats = [
            'total' => $seller->documents->count(),
            'pending' => $seller->documents->where('verification_status', 'pending')->count(),
            'verified' => $seller->documents->where('verification_status', 'verified')->count(),
            'rejected' => $seller->documents->where('verification_status', 'rejected')->count(),
        ];
        
        // Count bank accounts by verification status
        $banksStats = [
            'total' => $seller->bankAccounts->count(),
            'pending' => $seller->bankAccounts->where('verification_status', 'pending')->count(),
            'verified' => $seller->bankAccounts->where('verification_status', 'verified')->count(),
            'rejected' => $seller->bankAccounts->where('verification_status', 'rejected')->count(),
        ];
        
        return Inertia::render('Admin/SellerOverviewView', [
            'seller' => $seller,
            'documentsStats' => $documentsStats,
            'banksStats' => $banksStats,
            'resourceNeo' => $this->resourceNeo
        ]);
    }

    public function documents($id)
    {
        $seller = Seller::with(['documents.verifiedBy'])
            ->findOrFail($id);
        
        return Inertia::render('Admin/SellerDocumentsView', [
            'seller' => $seller,
            'documents' => $seller->documents,
            'resourceNeo' => $this->resourceNeo
        ]);
    }

    public function banks($id)
    {
        $seller = Seller::with(['bankAccounts'])
            ->findOrFail($id);
        
        return Inertia::render('Admin/SellerBanksView', [
            'seller' => $seller,
            'bankAccounts' => $seller->bankAccounts,
            'resourceNeo' => $this->resourceNeo
        ]);
    }

    public function verifyDocument(Request $request, $sellerId, $documentId)
    {
        $seller = Seller::findOrFail($sellerId);
        $document = $seller->documents()->findOrFail($documentId);
        
        $validated = $request->validate([
            'action' => 'required|in:verify,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:500'
        ]);
        
        if ($validated['action'] === 'verify') {
            $document->update([
                'verification_status' => 'verified',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
                'rejection_reason' => null,
            ]);
            
            \ActivityLog::add([
                'action' => 'verified',
                'module' => 'seller_document',
                'data_key' => $seller->business_name . ' - ' . $document->document_type
            ]);
            
            return redirect()->back()->with([
                'message' => 'Document verified successfully',
                'msg_type' => 'success'
            ]);
        } else {
            $document->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'verified_at' => null,
                'verified_by' => null,
            ]);
            
            \ActivityLog::add([
                'action' => 'rejected',
                'module' => 'seller_document',
                'data_key' => $seller->business_name . ' - ' . $document->document_type
            ]);
            
            return redirect()->back()->with([
                'message' => 'Document rejected',
                'msg_type' => 'warning'
            ]);
        }
    }

    public function verifyBank(Request $request, $sellerId, $bankId)
    {
        $seller = Seller::findOrFail($sellerId);
        $bankAccount = $seller->bankAccounts()->findOrFail($bankId);
        
        $validated = $request->validate([
            'action' => 'required|in:verify,reject',
        ]);
        
        if ($validated['action'] === 'verify') {
            $bankAccount->update([
                'verification_status' => 'verified',
            ]);
            
            \ActivityLog::add([
                'action' => 'verified',
                'module' => 'seller_bank',
                'data_key' => $seller->business_name . ' - ' . $bankAccount->account_number
            ]);
            
            return redirect()->back()->with([
                'message' => 'Bank account verified successfully',
                'msg_type' => 'success'
            ]);
        } else {
            $bankAccount->update([
                'verification_status' => 'rejected',
            ]);
            
            \ActivityLog::add([
                'action' => 'rejected',
                'module' => 'seller_bank',
                'data_key' => $seller->business_name . ' - ' . $bankAccount->account_number
            ]);
            
            return redirect()->back()->with([
                'message' => 'Bank account rejected',
                'msg_type' => 'warning'
            ]);
        }
    }
}
