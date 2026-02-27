<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\VariantType;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class CategoryController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'category',
        'resourceTitle' => 'Categories',
        'iconPath' => 'M17,13H13V17H11V13H7V11H11V7H13V11H17M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z',
        'actions' => ['c', 'r', 'u', 'd']
    ];

    public function __construct()
    {
        $this->middleware('can:category_list', ['only' => ['index', 'show']]);
        $this->middleware('can:category_create', ['only' => ['create', 'store']]);
        $this->middleware('can:category_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:category_delete', ['only' => ['destroy', 'bulkDestroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $formInfo = Category::formInfo();
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
        $resourceData = QueryBuilder::for(Category::class)
            ->with('parent')
            ->defaultSort('display_order')
            ->allowedSorts(array_merge(array_keys($formInfo), array_keys($formInfoMulti), []))
            ->allowedFilters(array_merge(array_keys($formInfo), array_keys($formInfoMulti), [$globalSearch]))
            ->paginate($perPage)
            ->withQueryString();
        
        // Append status_label, full_path and image_url to each category
        $resourceData->getCollection()->transform(function ($category) {
            $category->append(['status_label', 'full_path', 'image_url']);
            return $category;
        });

        // Add bulk actions if user has permission
        if (Auth::user()->can('category_delete')) {
            $this->resourceNeo['bulkActions'] = ['bulk_delete' => []];
        }
        if (Auth::user()->can('category_export')) {
            $this->resourceNeo['bulkActions']['csvExport'] = [];
        }

        return Inertia::render('Admin/CategoryIndexView', [
            'resourceData' => $resourceData,
            'resourceNeo' => $this->resourceNeo
        ])->table(function (InertiaTable $table) use ($formInfo, $formInfoMulti) {
            $table->withGlobalSearch();
            
            // Add columns from formInfo (exclude fields you don't want in table)
            $arrKey = array_diff(array_keys($formInfo), ['image', 'description', 'meta_title', 'meta_description', 'is_active', 'featured', 'parent_id', 'display_order']);
            
            // Add image column first
            $table->column('image_url', 'Image', searchable: false, sortable: false);

            foreach ($arrKey as $key) {
                $table->column(
                    $key,
                    $formInfo[$key]['label'],
                    searchable: $formInfo[$key]['searchable'] ?? false,
                    sortable: $formInfo[$key]['sortable'] ?? false,
                    hidden: $formInfo[$key]['hidden'] ?? false
                );
            }
            
            // Add full_path column instead of parent_id
            $table->column('full_path', 'Category Path', searchable: false, sortable: false);
            
            // Add status_label column instead of is_active
            $table->column('status_label', 'Status', searchable: false, sortable: false);
            
            // Add featured column
            $table->column('featured', 'Featured', searchable: false, sortable: true);
            
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
                ])
                ->selectFilter(key: 'featured', label: 'Featured', options: [
                    '1' => 'Yes',
                    '0' => 'No',
                ])
                ->selectFilter(key: 'parent_id', label: 'Parent Category', options: $this->getParentCategoryOptions());
        });
    }

    /**
 * Show the form for creating a new resource.
 */
public function create()
{
    $resourceNeo = $this->resourceNeo;
    $resourceNeo['formInfo'] = Category::formInfo();
    
    // Populate parent category options
    $resourceNeo['formInfo']['parent_id']['options'] = $this->getParentCategoryOptions();
    
    // Get all active variant types for assignment
    $variantTypes = VariantType::active()->ordered()->get()->map(function ($vt) {
        return [
            'id' => $vt->id,
            'name' => $vt->name,
            'input_type' => $vt->input_type,
            'input_type_label' => $vt->input_type_label,
        ];
    });
    
    return Inertia::render('Admin/CategoryAddEditView', [
        'resourceNeo' => $resourceNeo,
        'variantTypes' => $variantTypes,
        'categoryVariantTypes' => [],
        'formdata' => [],
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $formInfo = Category::formInfo();
        $attributeNames = [];
        $validateRule = [];
        $savedArray = [];
        
        // Extract parent_id from Multiselect object format BEFORE validation
        $parentIdValue = $request->parent_id;
        if (is_array($parentIdValue) && isset($parentIdValue['id'])) {
            $extractedParentId = $parentIdValue['id'];
        } else {
            $extractedParentId = $parentIdValue;
        }
        
        foreach (array_keys($formInfo) as $key) {
            $attributeNames[$key] = $formInfo[$key]['label'];
            if (isset($formInfo[$key]['vRule'])) {
                $validateRule[$key] = $formInfo[$key]['vRule'];
            }
            
            // Handle file upload separately
            if ($key === 'image' && $request->hasFile('image')) {
                $savedArray[$key] = $request->file('image')->store('categories/images');
            } elseif ($key === 'parent_id') {
                $savedArray[$key] = $extractedParentId;
            } else {
                $savedArray[$key] = $request->{$key};
            }
        }
        
        // Create a temporary request data array with extracted parent_id for validation
        $validationData = $request->all();
        $validationData['parent_id'] = $extractedParentId;
        
        // Validate using the modified data
        $validator = \Validator::make($validationData, $validateRule, [], $attributeNames);
        $validator->validate();
        
        $category = Category::create($savedArray);

        // Handle variant type assignments
        if ($request->has('variant_types') && is_array($request->variant_types)) {
            $syncData = [];
            foreach ($request->variant_types as $index => $vt) {
                if (isset($vt['variant_type_id'])) {
                    $syncData[$vt['variant_type_id']] = [
                        'display_order' => $index,
                    ];
                }
            }
            $category->variantTypes()->sync($syncData);
        }

        \ActivityLog::add([
            'action' => 'created',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $request->{array_keys($formInfo)[0]}
        ]);

        return redirect()->route('category.index')->with([
            'message' => 'Category Created Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
 * Show the form for editing the specified resource.
 */
public function edit(Category $category)
{
    $formdata = $category;
    $resourceNeo = $this->resourceNeo;
    $resourceNeo['formInfo'] = Category::formInfo();
    
    // Populate parent category options (exclude current category and its descendants)
    $resourceNeo['formInfo']['parent_id']['options'] = $this->getParentCategoryOptions($category->id);
    
    // Convert parent_id to option object format for Multiselect
    if ($category->parent_id) {
        $parentCategory = Category::find($category->parent_id);
        if ($parentCategory) {
            $formdata->parent_id = [
                'id' => $parentCategory->id,
                'label' => $parentCategory->full_path
            ];
        }
    } else {
        // Set to null option for top-level categories
        $formdata->parent_id = [
            'id' => null,
            'label' => '-- No Parent (Top Level) --'
        ];
    }
    
    // Add current image URL to formInfo for display
    if ($category->image) {
        $resourceNeo['formInfo']['image']['currentFile'] = Storage::url($category->image);
    }
    
    // Get all active variant types for assignment
    $variantTypes = VariantType::active()->ordered()->get()->map(function ($vt) {
        return [
            'id' => $vt->id,
            'name' => $vt->name,
            'input_type' => $vt->input_type,
            'input_type_label' => $vt->input_type_label,
        ];
    });
    
    // Get current category's variant type assignments
    $categoryVariantTypes = $category->categoryVariantTypes()
        ->with('variantType')
        ->orderBy('display_order', 'asc')
        ->get()
        ->map(function ($cvt) {
            return [
                'variant_type_id' => $cvt->variant_type_id,
            ];
    });
    
    return Inertia::render('Admin/CategoryAddEditView', [
        'formdata' => $formdata,
        'resourceNeo' => $resourceNeo,
        'variantTypes' => $variantTypes,
        'categoryVariantTypes' => $categoryVariantTypes,
    ]);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $formInfo = Category::formInfo();
        $attributeNames = [];
        $validateRule = [];
        
        // Extract parent_id from Multiselect object format BEFORE validation
        $parentIdValue = $request->parent_id;
        if (is_array($parentIdValue) && isset($parentIdValue['id'])) {
            $extractedParentId = $parentIdValue['id'];
        } else {
            $extractedParentId = $parentIdValue;
        }
        
        foreach (array_keys($formInfo) as $key) {
            $attributeNames[$key] = $formInfo[$key]['label'];
            if (isset($formInfo[$key]['vRule'])) {
                $validateRule[$key] = $formInfo[$key]['vRule'];
            }
        }
        
        // Update unique validation rules to ignore current record
        $validateRule['slug'] = 'required|string|max:255|alpha_dash|unique:categories,slug,' . $category->id;
        
        // Remove image validation if no new file is uploaded
        if (!$request->hasFile('image')) {
            unset($validateRule['image']);
        }
        
        // Create a temporary request data array with extracted parent_id for validation
        $validationData = $request->all();
        $validationData['parent_id'] = $extractedParentId;
        
        // Validate using the modified data
        $validator = \Validator::make($validationData, $validateRule, [], $attributeNames);
        $validator->validate();
        
        // Handle file upload
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::delete($category->image);
            }
            $category->image = $request->file('image')->store('categories/images');
        }
        
        // Handle parent_id - use the extracted value
        if ($request->has('parent_id')) {
            $category->parent_id = $extractedParentId;
        }
        
        // Update other fields (only if they exist in request)
        foreach (array_diff(array_keys($formInfo), ['image', 'parent_id']) as $key) {
            if ($request->has($key)) {
                $category->{$key} = $request->{$key};
            }
        }

        $category->save();

        // Handle variant type assignments
        if ($request->has('variant_types')) {
            $variantTypesData = $request->variant_types;
            if (is_array($variantTypesData) && count($variantTypesData) > 0) {
                $syncData = [];
                foreach ($variantTypesData as $index => $vt) {
                    if (isset($vt['variant_type_id'])) {
                        $syncData[$vt['variant_type_id']] = [
                            'display_order' => $index,
                        ];
                    }
                }
                $category->variantTypes()->sync($syncData);
            } else {
                // If empty or null, remove all variant types
                $category->variantTypes()->sync([]);
            }
        }

        \ActivityLog::add([
            'action' => 'updated',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $category->name
        ]);

        return redirect()->route('category.index')->with([
            'message' => 'Category Updated Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has children
        if ($category->children()->count() > 0) {
            return redirect()->back()->with([
                'message' => 'Cannot delete category with subcategories. Please delete subcategories first.',
                'msg_type' => 'error'
            ]);
        }
        
        // Check if category has products (when implemented)
        // if ($category->products()->count() > 0) {
        //     return redirect()->back()->with([
        //         'message' => 'Cannot delete category with products. Please reassign products first.',
        //         'msg_type' => 'error'
        //     ]);
        // }
        
        // Delete associated files if needed
        if ($category->image) {
            Storage::delete($category->image);
        }

        $categoryName = $category->name;
        $category->delete();

        \ActivityLog::add([
            'action' => 'deleted',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $categoryName
        ]);

        return redirect()->route('category.index')->with([
            'message' => 'Category Deleted Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDestroy()
    {
        $categories = Category::whereIn('id', request('ids'))->get();
        
        // Check if any category has children
        foreach ($categories as $category) {
            if ($category->children()->count() > 0) {
                return redirect()->back()->with([
                    'message' => 'Cannot delete categories with subcategories. Please delete subcategories first.',
                    'msg_type' => 'error'
                ]);
            }
        }
        
        // Delete images for all categories
        foreach ($categories as $category) {
            if ($category->image) {
                Storage::delete($category->image);
            }
        }
        
        Category::whereIn('id', request('ids'))->delete();
        
        $uname = (count(request('ids')) > 50) ? 'Many' : implode(',', request('ids'));
        \ActivityLog::add([
            'action' => 'deleted',
            'module' => $this->resourceNeo['resourceName'],
            'data_key' => $uname
        ]);
        
        return redirect()->back()->with([
            'message' => 'Selected Categories Deleted Successfully!',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Get parent category options for dropdown.
     *
     * @param int|null $excludeId Category ID to exclude (for edit mode)
     * @return array
     */
    private function getParentCategoryOptions($excludeId = null)
    {
        $query = Category::active()->ordered();
        
        if ($excludeId) {
            // Exclude current category and its descendants
            $query->where('id', '!=', $excludeId);
            $category = Category::find($excludeId);
            if ($category) {
                $descendantIds = $this->getDescendantIds($category);
                if (!empty($descendantIds)) {
                    $query->whereNotIn('id', $descendantIds);
                }
            }
        }
        
        $categories = $query->get();
        $options = [['id' => null, 'label' => '-- No Parent (Top Level) --']];
        
        foreach ($categories as $category) {
            $options[] = [
                'id' => $category->id,
                'label' => $category->full_path
            ];
        }
        
        return $options;
    }

    /**
     * Get all descendant IDs of a category.
     *
     * @param Category $category
     * @return array
     */
    private function getDescendantIds(Category $category)
    {
        $ids = [];
        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }
}
