<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'page',
        'resourceTitle' => 'Pages',
        'iconPath' => 'M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z',
        'actions' => ['c', 'r', 'u', 'd'],
    ];

    public function __construct()
    {
        $this->middleware('can:page_list', ['only' => ['index', 'show']]);
        $this->middleware('can:page_create', ['only' => ['create', 'store']]);
        $this->middleware('can:page_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:page_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                Collection::wrap($value)->each(function ($value) use ($query) {
                    $query
                        ->orWhere('title', 'LIKE', "%{$value}%")
                        ->orWhere('slug', 'LIKE', "%{$value}%");
                });
            });
        });

        $perPage = request()->query('perPage') ?? 10;

        $resourceData = QueryBuilder::for(Page::query())
            ->defaultSort('-updated_at')
            ->allowedSorts(['title', 'slug', 'is_active', 'updated_at'])
            ->allowedFilters(['title', 'slug', 'is_active', $globalSearch])
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/IndexView', [
            'resourceData' => $resourceData,
            'resourceNeo' => $this->resourceNeo,
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('title', 'Title', searchable: true, sortable: true)
                ->column('slug', 'Slug', searchable: true, sortable: true)
                ->column('is_active', 'Active', searchable: false, sortable: true)
                ->column('updated_at', 'Updated', searchable: false, sortable: true)
                ->column(label: 'Actions')
                ->selectFilter(key: 'is_active', label: 'Status', options: [
                    '1' => 'Active',
                    '0' => 'Inactive',
                ])
                ->perPageOptions([10, 15, 30, 50, 100]);
        });
    }

    public function create()
    {
        return Inertia::render('Admin/PageAddEditView');
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        Page::create($validated);

        return redirect()->route('page.index')->with([
            'message' => 'Page created successfully.',
            'msg_type' => 'success',
        ]);
    }

    public function edit(Page $page)
    {
        return Inertia::render('Admin/PageAddEditView', [
            'model' => $page,
        ]);
    }

    public function show(Page $page)
    {
        return redirect()->route('page.edit', $page);
    }

    public function update(Request $request, Page $page)
    {
        $validated = $this->validateRequest($request, $page->id);

        $page->update($validated);

        return redirect()->route('page.index')->with([
            'message' => 'Page updated successfully.',
            'msg_type' => 'success',
        ]);
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('page.index')->with([
            'message' => 'Page deleted successfully.',
            'msg_type' => 'success',
        ]);
    }

    protected function validateRequest(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($ignoreId)],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['show_in_footer'] = false;
        $validated['footer_section'] = null;
        $validated['footer_order'] = 0;

        return $validated;
    }
}
