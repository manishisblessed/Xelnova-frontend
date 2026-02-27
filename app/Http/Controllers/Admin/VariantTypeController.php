<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VariantType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class VariantTypeController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'variantType',
        'resourceTitle' => 'Variant Type',
        'iconPath' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
        'actions' => ['c', 'r', 'u', 'd'],
        'globalSearch' => [
            'name',
            'slug',
        ],
        'allowedSorts' => [
            'id',
            'name',
            'input_type',
            'is_active',
            'display_order',
            'created_at',
        ],
        'allowedFilters' => [
            'name',
            'input_type',
            'is_active',
        ],
        'allowedFilterExact' => [
            'is_active',
        ],
        'paginate' => 20,
        'withQueryString' => true,
        'columns' => [
            [
                'key' => 'id',
                'label' => 'ID',
                'sortable' => true,
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'slug',
                'label' => 'Slug',
                'sortable' => false,
            ],
            [
                'key' => 'input_type_label',
                'label' => 'Type',
                'sortable' => false,
            ],
            [
                'key' => 'display_order',
                'label' => 'Order',
                'sortable' => true,
            ],
            [
                'key' => 'status_label',
                'label' => 'Status',
                'sortable' => false,
            ],
        ],
    ];

    public function __construct()
    {
        $this->middleware('can:variantType_list', ['only' => ['index', 'show']]);
        $this->middleware('can:variantType_create', ['only' => ['create', 'store']]);
        $this->middleware('can:variantType_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:variantType_delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of variant types.
     */
    public function index()
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                foreach ($this->resourceNeo['globalSearch'] as $field) {
                    $query->orWhere($field, 'LIKE', "%{$value}%");
                }
            });
        });

        $variantTypes = QueryBuilder::for(VariantType::class)
            ->defaultSort('-id')
            ->allowedSorts($this->resourceNeo['allowedSorts'])
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('is_active'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('input_type'),
            ])
            ->paginate($this->resourceNeo['paginate'])
            ->withQueryString();

        return Inertia::render('Admin/IndexView', [
            'resourceNeo' => $this->resourceNeo,
            'resourceData' => $variantTypes,
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch();

            foreach ($this->resourceNeo['columns'] as $column) {
                $table->column(
                    key: $column['key'],
                    label: $column['label'],
                    canBeHidden: true,
                    sortable: $column['sortable'] ?? false,
                    searchable: $column['searchable'] ?? false
                );
            }

            $table->selectFilter('is_active', [
                '' => 'All Status',
                '1' => 'Active',
                '0' => 'Inactive',
            ], 'Status');

            $table->selectFilter('input_type', [
                '' => 'All Types',
                'color' => 'Color Picker',
                'size' => 'Size Selector',
                'text' => 'Text Input',
                'select' => 'Dropdown Select',
            ], 'Input Type');

            $table->column(label: 'Actions');
        });
    }

    /**
     * Show the form for creating a new variant type.
     */
    public function create()
    {
        $resourceNeo = $this->resourceNeo;
        $resourceNeo['formInfo'] = $this->getFormInfo();
        
        return Inertia::render('Admin/AddEditView', [
            'resourceNeo' => $resourceNeo,
            'formdata' => [],
        ]);
    }

    /**
     * Store a newly created variant type.
     */
    public function store(Request $request)
    {
        // Extract input_type from Multiselect object format
        $inputTypeValue = $request->input_type;
        if (is_array($inputTypeValue) && isset($inputTypeValue['id'])) {
            $inputTypeValue = $inputTypeValue['id'];
        }
        
        $validationData = $request->all();
        $validationData['input_type'] = $inputTypeValue;
        
        $validated = validator($validationData, [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|alpha_dash|unique:variant_types,slug',
            'input_type' => 'required|in:color,size,text,select',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ])->validate();

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure unique slug
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (VariantType::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        VariantType::create($validated);

        return Redirect::route('variantType.index')->with([
            'message' => 'Variant type created successfully.',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Display the specified variant type.
     */
    public function show(VariantType $variantType)
    {
        $resourceNeo = $this->resourceNeo;
        $resourceNeo['formInfo'] = $this->getFormInfo();
        
        return Inertia::render('Admin/AddEditView', [
            'resourceNeo' => $resourceNeo,
            'formdata' => $variantType,
            'isShow' => true,
        ]);
    }

    /**
     * Show the form for editing the specified variant type.
     */
    public function edit(VariantType $variantType)
    {
        $resourceNeo = $this->resourceNeo;
        $resourceNeo['formInfo'] = $this->getFormInfo();
        
        // Transform input_type to object for Multiselect
        $inputTypeOptions = $resourceNeo['formInfo']['input_type']['options'];
        foreach ($inputTypeOptions as $option) {
            if ($option['id'] === $variantType->input_type) {
                $variantType->input_type = $option;
                break;
            }
        }
        
        return Inertia::render('Admin/AddEditView', [
            'resourceNeo' => $resourceNeo,
            'formdata' => $variantType,
        ]);
    }

    /**
     * Update the specified variant type.
     */
    public function update(Request $request, VariantType $variantType)
    {
        // Extract input_type from Multiselect object format
        $inputTypeValue = $request->input_type;
        if (is_array($inputTypeValue) && isset($inputTypeValue['id'])) {
            $inputTypeValue = $inputTypeValue['id'];
        }
        
        $validationData = $request->all();
        $validationData['input_type'] = $inputTypeValue;
        
        $validated = validator($validationData, [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|alpha_dash|unique:variant_types,slug,' . $variantType->id,
            'input_type' => 'required|in:color,size,text,select',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ])->validate();

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure unique slug
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (VariantType::where('slug', $validated['slug'])->where('id', '!=', $variantType->id)->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        $variantType->update($validated);

        return Redirect::route('variantType.index')->with([
            'message' => 'Variant type updated successfully.',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Remove the specified variant type.
     */
    public function destroy(VariantType $variantType)
    {
        // Check if variant type is being used by any categories
        if ($variantType->categories()->count() > 0) {
            return Redirect::back()->with([
                'message' => 'Cannot delete variant type that is assigned to categories.',
                'msg_type' => 'danger'
            ]);
        }

        // Check if variant type is being used by any products
        if ($variantType->productVariantOptions()->count() > 0) {
            return Redirect::back()->with([
                'message' => 'Cannot delete variant type that is used in products.',
                'msg_type' => 'danger'
            ]);
        }

        $variantType->delete();

        return Redirect::route('variantType.index')->with([
            'message' => 'Variant type deleted successfully.',
            'msg_type' => 'success'
        ]);
    }

    /**
     * Get form field definitions for the variant type.
     */
    protected function getFormInfo(): array
    {
        return [
            'name' => [
                'label' => 'Variant Type Name',
                'type' => 'input',
                'default' => '',
                'vRule' => 'required|string|max:100',
                'tooltip' => 'Name of the variant type (e.g., Color, Size, RAM)',
            ],
            'slug' => [
                'label' => 'Slug',
                'type' => 'input',
                'default' => '',
                'vRule' => 'nullable|string|max:100|alpha_dash',
                'tooltip' => 'URL-friendly identifier (auto-generated if empty)',
            ],
            'input_type' => [
                'label' => 'Input Type',
                'type' => 'select',
                'default' => 'select',
                'vRule' => 'required|in:color,size,text,select',
                'tooltip' => 'How this variant should be displayed on the product page',
                'options' => [
                    ['id' => 'color', 'label' => 'Color Picker (shows color swatches)'],
                    ['id' => 'size', 'label' => 'Size Selector (shows size buttons)'],
                    ['id' => 'text', 'label' => 'Text Input (free text entry)'],
                    ['id' => 'select', 'label' => 'Dropdown Select (dropdown menu)'],
                ],
            ],
            'display_order' => [
                'label' => 'Display Order',
                'type' => 'number',
                'default' => 0,
                'vRule' => 'nullable|integer|min:0',
                'tooltip' => 'Order in which this variant type appears (lower = first)',
            ],
            'is_active' => [
                'label' => 'Active',
                'type' => 'switch',
                'default' => true,
                'vRule' => 'sometimes|nullable|boolean',
                'tooltip' => 'Is this variant type available for use?',
            ],
        ];
    }
}
