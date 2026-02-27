<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class BrandController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'brand',
        'resourceTitle' => 'Brands',
        'iconPath' => 'M5.5,9A1.5,1.5 0 0,0 7,7.5A1.5,1.5 0 0,0 5.5,6A1.5,1.5 0 0,0 4,7.5A1.5,1.5 0 0,0 5.5,9M17.41,11.58C17.77,11.94 18,12.44 18,13C18,13.55 17.78,14.05 17.41,14.41L12.41,19.41C12.05,19.77 11.55,20 11,20C10.45,20 9.95,19.78 9.58,19.41L2.59,12.42C2.22,12.05 2,11.55 2,11V6C2,4.89 2.89,4 4,4H9C9.55,4 10.05,4.22 10.41,4.58L17.41,11.58M13.54,5.71L14.54,4.71L21.41,11.58C21.78,11.94 22,12.45 22,13C22,13.55 21.78,14.05 21.42,14.41L16.04,19.79L15.04,18.79L20.75,13L13.54,5.71Z',
        'actions' => ['c', 'r', 'u', 'd']
    ];

    public function __construct()
    {
        $this->middleware('can:brand_list', ['only' => ['index', 'show']]);
        $this->middleware('can:brand_create', ['only' => ['create', 'store']]);
        $this->middleware('can:brand_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:brand_delete', ['only' => ['destroy', 'bulkDestroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formInfo = Brand::formInfo();
        $formInfoMulti = [];
        
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) use ($formInfo, $formInfoMulti) {
            $query->where(function ($query) use ($value, $formInfo, $formInfoMulti) {
                Collection::wrap($value)->each(function ($value) use ($query, $formInfo, $formInfoMulti) {
                    foreach (array_keys($formInfo) as $key) {
                        $query->orWhere($key, 'LIKE', "%{$value}%");
                    }
                    foreach (array_keys($formInfoMulti) as $key) {
                        $query->orWhere($key, 'LIKE', "%{$value}%");
                    }
                });
            });
        });

        $perPage = request()->query('perPage') ?? 10;
        $resourceData = QueryBuilder::for(Brand::class)
            ->defaultSort('name')
            ->allowedSorts(array_merge(array_keys($formInfo), array_keys($formInfoMulti), []))
            ->allowedFilters(array_merge(array_keys($formInfo), array_keys($formInfoMulti), [$globalSearch]))
            ->paginate($perPage)
            ->withQueryString();
        
        // Append status_label and logo_url to each brand
        $resourceData->getCollection()->transform(function ($brand) {
            $brand->append(['status_label', 'logo_url']);
            return $brand;
        });

        // Add bulk actions if user has permission
        if (Auth::user()->can('brand_delete')) {
            $this->resourceNeo['bulkActions'] = ['bulk_delete' => []];
        }
        if (Auth::user()->can('brand_export')) {
            $this->resourceNeo['bulkActions']['csvExport'] = [];
        }

        return Inertia::render('Admin/BrandIndexView', [
            'resourceData' => $resourceData,
            'resourceNeo' => $this->resourceNeo
        ])->table(function (InertiaTable $table) use ($formInfo, $formInfoMulti) {
            $table->withGlobalSearch();
            
            // Add columns from formInfo (exclude fields you don't want in table)
            $arrKey = array_diff(array_keys($formInfo), ['logo', 'description', 'meta_title', 'meta_description', 'is_active', 'slug']);
            
            // Add logo column first
            $table->column('logo_url', 'Logo', searchable: false, sortable: false);

            foreach ($arrKey as $key) {
                $table->column(
                    $key,
                    $formInfo[$key]['label'],
                    searchable: $formInfo[$key]['searchable'] ?? false,
                    sortable: $formInfo[$key]['sortable'] ?? false,
                    hidden: $formInfo[$key]['hidden'] ?? false
                );
            }
            
            // Add status_label column instead of is_active
            $table->column('status_label', 'Status', searchable: false, sortable: false);
            
            // Add columns from formInfoMulti
            foreach (array_keys($formInfoMulti) as $key) {
                $table->column(
                    $key,
                    $formInfoMulti[$key]['label'],
                    searchable: $formInfoMulti[$key]['searchable'] ?? false,
                    sortable: $formInfoMulti[$key]['sortable'] ?? false,
                    hidden: $formInfoMulti[$key]['hidden'] ?? false
                );
            }
            
            $table
                ->column(label: 'Actions')
                ->perPageOptions([10, 15, 30, 50, 100])
                ->selectFilter(key: 'is_active', label: 'Status', options: [
                    '1' => 'Active',
                    '0' => 'Inactive',
                ]);
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $resourceNeo = $this->resourceNeo;
        $resourceNeo['formInfo'] = Brand::formInfo();
        return Inertia::render('Admin/AddEditView', compact('resourceNeo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $formInfo = Brand::formInfo();
        $attributeNames = [];
        $validateRule = [];
        $savedArray = [];
        
        foreach (array_keys($formInfo) as $key) {
            $attributeNames[$key] = $formInfo[$key]['label'];
            if (isset($formInfo[$key]['vRule'])) {
                $validateRule[$key] = $formInfo[$key]['vRule'];
            }
            
            // Handle file upload separately
            if ($key === 'logo' && $request->hasFile('logo')) {
                $savedArray[$key] = $request->file('logo')->store('brands/logos');
            } else {
                $savedArray[$key] = $request->{$key};
            }
        }

        $request->validate($validateRule, [], $attributeNames);
        Brand::create($savedArray);

        \ActivityLog::add([
            'action' => 'created',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $request->{array_keys($formInfo)[0]}
        ]);

        return redirect()->route('brand.index')->with([
            'message' => 'Brand Created Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        $formdata = $brand;
        $resourceNeo = $this->resourceNeo;
        $resourceNeo['formInfo'] = Brand::formInfo();
        
        // Add current logo URL to formInfo for display
        if ($brand->logo) {
            $resourceNeo['formInfo']['logo']['currentFile'] = Storage::url($brand->logo);
        }
        
        return Inertia::render('Admin/AddEditView', compact('formdata', 'resourceNeo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $formInfo = Brand::formInfo();
        $attributeNames = [];
        $validateRule = [];
        
        foreach (array_keys($formInfo) as $key) {
            $attributeNames[$key] = $formInfo[$key]['label'];
            if (isset($formInfo[$key]['vRule'])) {
                $validateRule[$key] = $formInfo[$key]['vRule'];
            }
        }
        
        // Update unique validation rules to ignore current record
        $validateRule['name'] = 'required|string|max:255|unique:brands,name,' . $brand->id;
        $validateRule['slug'] = 'required|string|max:255|alpha_dash|unique:brands,slug,' . $brand->id;
        
        // Remove logo validation if no new file is uploaded
        if (!$request->hasFile('logo')) {
            unset($validateRule['logo']);
        }
        
        $request->validate($validateRule, [], $attributeNames);
        
        // Handle file upload
        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::delete($brand->logo);
            }
            $brand->logo = $request->file('logo')->store('brands/logos');
        }
        
        // Update other fields (only if they exist in request)
        foreach (array_diff(array_keys($formInfo), ['logo']) as $key) {
            if ($request->has($key)) {
                $brand->{$key} = $request->{$key};
            }
        }

        $brand->save();

        \ActivityLog::add([
            'action' => 'updated',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $brand->name
        ]);

        return redirect()->route('brand.index')->with([
            'message' => 'Brand Updated Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        // Delete logo if exists
        if ($brand->logo) {
            Storage::delete($brand->logo);
        }

        $brandName = $brand->name;
        $brand->delete();

        \ActivityLog::add([
            'action' => 'deleted',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $brandName
        ]);

        return redirect()->route('brand.index')->with([
            'message' => 'Brand Deleted Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Bulk delete brands.
     */
    public function bulkDestroy()
    {
        $brands = Brand::whereIn('id', request('ids'))->get();
        
        // Delete logos for all brands
        foreach ($brands as $brand) {
            if ($brand->logo) {
                Storage::delete($brand->logo);
            }
        }
        
        Brand::whereIn('id', request('ids'))->delete();
        
        $uname = (count(request('ids')) > 50) ? 'Many' : implode(',', request('ids'));
        \ActivityLog::add([
            'action' => 'deleted',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $uname
        ]);
        
        return redirect()->back()->with([
            'message' => 'Selected Brands Deleted Successfully!',
            'msg_type' => 'success'
        ]);
    }
}
