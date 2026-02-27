<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Illuminate\Support\Facades\Storage;

class SellerBrandController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'sellerBrand',
        'resourceTitle' => 'Seller Brands',
        'iconPath' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z',
        'actions' => ['r', 'd'] // Only read (view) and delete - brands are created by sellers
    ];



    /**
     * Display a listing of seller brands
     */
    public function index()
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                Collection::wrap($value)->each(function ($value) use ($query) {
                    $query
                        ->orWhere('brand_name', 'LIKE', "%{$value}%")
                        ->orWhereHas('seller', function ($q) use ($value) {
                            $q->where('business_name', 'LIKE', "%{$value}%");
                        });
                });
            });
        });

        $perPage = request()->query('perPage') ?? 15;

        $brands = QueryBuilder::for(SellerBrand::class)
            ->with(['seller'])
            ->defaultSort('-created_at')
            ->allowedSorts(['brand_name', 'approval_status', 'created_at'])
            ->allowedFilters([
                'brand_name',
                'approval_status',
                AllowedFilter::exact('seller_id'),
                $globalSearch
            ])
            ->paginate($perPage)
            ->withQueryString();

        // Get sellers for filter dropdown
        $sellersQuery = \App\Models\Seller::orderBy('business_name')->get();
        $sellers = ['' => 'All Sellers'];
        foreach ($sellersQuery as $seller) {
            $sellers[$seller->id] = $seller->business_name;
        }


        // Add extra links for actions
        $this->resourceNeo['extraLinks'] = [
            [
                'label' => 'View Details',
                'link' => 'sellerBrand.show',
                'icon' => 'M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z',
                'color' => 'info',
                'conditions' => [['key' => 'id', 'cond' => '*', 'compvl' => '']]
            ],
        ];



        return Inertia::render('Admin/IndexView', [
            'resourceData' => $brands,
            'resourceNeo' => $this->resourceNeo,
        ])->table(function (InertiaTable $table) use ($sellers) {
            $table->withGlobalSearch()
                ->column('id', 'ID', searchable: false, sortable: true)
                ->column('brand_name', 'Brand Name', searchable: true, sortable: true)
                ->column('seller_business_name', 'Seller', searchable: false, sortable: false)
                ->column('approval_status_label', 'Status', searchable: false, sortable: false)
                ->column('created_at', 'Submitted', searchable: false, sortable: true)
                ->column(label: 'Actions')
                ->selectFilter('seller_id', $sellers, 'Seller')
                ->selectFilter('approval_status', [
                    '' => 'All Statuses',
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ], 'Status')
                ->perPageOptions([10, 15, 25, 50, 100]);
        });
    }

    /**
     * Display the specified brand
     */
    public function show(SellerBrand $sellerBrand)
    {
        $sellerBrand->load(['seller']);

        // Add file URLs
        if ($sellerBrand->logo_path) {
            $sellerBrand->logo_url = Storage::url($sellerBrand->logo_path);
        }
        if ($sellerBrand->proof_document_path) {
            $sellerBrand->proof_url = Storage::url($sellerBrand->proof_document_path);
        }

        return Inertia::render('Admin/SellerBrandShowView', [
            'brand' => $sellerBrand,
            'resourceNeo' => $this->resourceNeo,
        ]);
    }

    /**
     * Approve a brand
     */
    public function approve(Request $request, $id)
    {
        $brand = SellerBrand::findOrFail($id);

        $brand->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('message', 'Brand approved successfully!');
    }

    /**
     * Reject a brand
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $brand = SellerBrand::findOrFail($id);

        $brand->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_at' => null,
        ]);

        return back()->with('message', 'Brand rejected.');
    }

    /**
     * Delete a brand
     */
    public function destroy(SellerBrand $sellerBrand)
    {
        $sellerBrand->delete();

        return redirect()->route('sellerBrand.index')
            ->with('message', 'Brand deleted successfully!');
    }
}
