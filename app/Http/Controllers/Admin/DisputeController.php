<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class DisputeController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'dispute',
        'resourceTitle' => 'Disputes',
        'iconPath' => 'M13,13H11V7H13M13,17H11V15H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z',
        'actions' => 'r', // Read-only, resolve via custom action
    ];

    public function __construct()
    {
        $this->middleware('can:dispute_list', ['only' => ['index']]);
        $this->middleware('can:dispute_view', ['only' => ['show']]);
        $this->middleware('can:dispute_resolve', ['only' => ['resolve', 'updateStatus']]);
    }

    /**
     * Display a listing of disputes
     */
    public function index()
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                Collection::wrap($value)->each(function ($value) use ($query) {
                    $query
                        ->orWhere('dispute_number', 'LIKE', "%{$value}%")
                        ->orWhere('subject', 'LIKE', "%{$value}%")
                        ->orWhereHas('user', function ($q) use ($value) {
                            $q->where('name', 'LIKE', "%{$value}%")
                              ->orWhere('email', 'LIKE', "%{$value}%");
                        })
                        ->orWhereHas('order', function ($q) use ($value) {
                            $q->where('order_number', 'LIKE', "%{$value}%");
                        });
                });
            });
        });

        $perPage = request()->query('perPage') ?? 15;

        $disputes = QueryBuilder::for(Dispute::class)
            ->with(['user', 'order', 'resolver'])
            ->defaultSort('-created_at')
            ->allowedSorts(['dispute_number', 'subject', 'status', 'priority', 'type', 'created_at'])
            ->allowedFilters([
                'dispute_number',
                'subject',
                'status',
                'priority',
                'type',
                $globalSearch
            ])
            ->paginate($perPage)
            ->withQueryString();

        // Add extra links for actions
        $this->resourceNeo['extraLinks'] = [
            [
                'routeName' => 'dispute.show',
                'label' => 'View',
                'color' => 'info',
                'icon' => 'mdiEye',
            ],
        ];

        return Inertia::render('Admin/IndexView', [
            'resourceData' => $disputes,
            'resourceNeo' => $this->resourceNeo,
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('dispute_number', 'Dispute #', searchable: true, sortable: true)
                ->column('order_number', 'Order #', searchable: false, sortable: false)
                ->column('customer_name', 'Customer', searchable: false, sortable: false)
                ->column('type_label', 'Type', searchable: false, sortable: true)
                ->column('subject', 'Subject', searchable: true, sortable: true)
                ->column('status_label', 'Status', searchable: false, sortable: true)
                ->column('priority_label', 'Priority', searchable: false, sortable: true)
                ->column('created_at', 'Created', searchable: false, sortable: true)
                ->column(label: 'Actions')
                ->selectFilter('status', [
                    '' => 'All Statuses',
                    'open' => 'Open',
                    'under_review' => 'Under Review',
                    'resolved' => 'Resolved',
                    'rejected' => 'Rejected',
                    'closed' => 'Closed',
                ], 'Status')
                ->selectFilter('priority', [
                    '' => 'All Priorities',
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'urgent' => 'Urgent',
                ], 'Priority')
                ->selectFilter('type', [
                    '' => 'All Types',
                    'product_issue' => 'Product Issue',
                    'delivery_issue' => 'Delivery Issue',
                    'payment_issue' => 'Payment Issue',
                    'seller_issue' => 'Seller Issue',
                    'other' => 'Other',
                ], 'Type')
                ->perPageOptions([10, 15, 25, 50, 100]);
        });
    }

    /**
     * Display the specified dispute
     */
    public function show(Dispute $dispute)
    {
        $dispute->load(['user', 'order.items.product', 'subOrder', 'resolver']);

        return Inertia::render('Admin/DisputeShowView', [
            'dispute' => $dispute,
            'resourceNeo' => $this->resourceNeo,
            'statuses' => Dispute::STATUSES,
            'priorities' => Dispute::PRIORITIES,
            'types' => Dispute::TYPES,
        ]);
    }

    /**
     * Resolve a dispute
     */
    public function resolve(Request $request, Dispute $dispute)
    {
        $request->validate([
            'resolution' => 'required|string|min:10',
            'action' => 'required|in:resolve,reject',
        ]);

        if (!$dispute->canBeResolved()) {
            return redirect()->back()->with([
                'message' => 'This dispute cannot be resolved in its current status.',
                'msg_type' => 'danger'
            ]);
        }

        if ($request->action === 'resolve') {
            $dispute->resolve($request->resolution, auth()->id());
            $message = 'Dispute resolved successfully.';
        } else {
            $dispute->reject($request->resolution, auth()->id());
            $message = 'Dispute rejected.';
        }

        \ActivityLog::add([
            'action' => $request->action === 'resolve' ? 'resolved' : 'rejected',
            'module' => 'dispute',
            'data_key' => $dispute->dispute_number
        ]);

        return redirect()->route('dispute.index')->with([
            'message' => $message,
            'msg_type' => 'success'
        ]);
    }

    /**
     * Update dispute status
     */
    public function updateStatus(Request $request, Dispute $dispute)
    {
        $request->validate([
            'status' => 'required|in:open,under_review,resolved,rejected,closed',
        ]);

        $oldStatus = $dispute->status;
        $dispute->update(['status' => $request->status]);

        \ActivityLog::add([
            'action' => 'updated',
            'module' => 'dispute',
            'data_key' => $dispute->dispute_number . ' status: ' . $oldStatus . ' → ' . $request->status
        ]);

        return redirect()->back()->with([
            'message' => 'Dispute status updated.',
            'msg_type' => 'success'
        ]);
    }
}
