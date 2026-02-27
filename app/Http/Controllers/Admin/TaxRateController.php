<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TaxRateController extends Controller
{
    public function index()
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('name', 'LIKE', "%{$value}%");
            });
        });

        $taxRates = QueryBuilder::for(TaxRate::class)
            ->defaultSort('rate')
            ->allowedSorts(['name', 'rate', 'is_active'])
            ->allowedFilters(['name', 'is_active', $globalSearch])
            ->paginate(request()->query('perPage') ?? 10)
            ->withQueryString();

        return Inertia::render('Admin/TaxRateIndexView', ['taxRates' => $taxRates])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('name', 'Name', searchable: true, sortable: true)
                ->column('rate', 'Rate (%)', searchable: false, sortable: true)
                ->column('is_active', 'Status', searchable: false, sortable: true)
                ->column(label: 'Actions');
        });
    }

    public function create()
    {
        return Inertia::render('Admin/TaxRateAddEditView');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100|unique:tax_rates,rate',
            'is_active' => 'boolean',
        ]);

        TaxRate::create($validated);

        return redirect()->route('tax-rate.index')->with('message', 'Tax Rate created successfully.');
    }

    public function edit(TaxRate $taxRate)
    {
        return Inertia::render('Admin/TaxRateAddEditView', ['model' => $taxRate]);
    }

    public function update(Request $request, TaxRate $taxRate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100|unique:tax_rates,rate,' . $taxRate->id,
            'is_active' => 'boolean',
        ]);

        $taxRate->update($validated);

        return redirect()->route('tax-rate.index')->with('message', 'Tax Rate updated successfully.');
    }

    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();
        return redirect()->route('tax-rate.index')->with('message', 'Tax Rate deleted successfully.');
    }
}
