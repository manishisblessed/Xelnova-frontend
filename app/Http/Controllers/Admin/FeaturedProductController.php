<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class FeaturedProductController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'featuredProduct',
        'resourceTitle' => 'Featured Products',
        'iconPath' => 'M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z',
        'actions' => 'cd',
        'actionExpand' => false,
    ];

    public function __construct()
    {
        $this->middleware('can:featuredProduct_list', ['only' => ['index']]);
        $this->middleware('can:featuredProduct_create', ['only' => ['create', 'store']]);
        $this->middleware('can:featuredProduct_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $query = Product::where('is_featured', true)
            ->with(['seller', 'category', 'images']);
        
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('name', 'LIKE', "%{$value}%")
                      ->orWhere('sku', 'LIKE', "%{$value}%");
            });
        });
        
        $perPage = request()->query('perPage') ?? 15;
        $products = QueryBuilder::for($query)
            ->defaultSort('-featured_at')
            ->allowedSorts(['name', 'price', 'featured_at'])
            ->allowedFilters(['status', $globalSearch])
            ->paginate($perPage)
            ->withQueryString();
        
        // Add formatted data
        $products->getCollection()->transform(function ($product) {
            $product->formatted_price = '₹' . number_format($product->price, 2);
            $product->seller_name = $product->seller?->seller?->business_name ?? $product->seller?->name ?? 'Unknown';
            $product->category_name = $product->category->name ?? 'Uncategorized';
            return $product;
        });
        
        return Inertia::render('Admin/FeaturedProductIndexView', [
            'resourceData' => $products,
            'resourceNeo' => $this->resourceNeo,
        ])->table(function (InertiaTable $table) {
            $table->withGlobalSearch()
                ->column('name', 'Product', searchable: true, sortable: true)
                ->column('seller_name', 'Seller', searchable: false, sortable: false)
                ->column('category_name', 'Category', searchable: false, sortable: false)
                ->column('formatted_price', 'Price', searchable: false, sortable: true)
                ->column('status', 'Status', searchable: false, sortable: false)
                ->column(label: 'Actions')
                ->perPageOptions([15, 30, 50, 100]);
        });
    }

    public function create()
    {
        // Get approved products that are not featured
        $availableProducts = Product::where('status', 'approved')
            ->where('is_featured', false)
            ->with(['seller', 'category'])
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'label' => $product->name . ' - ' . ($product->seller?->seller?->business_name ?? $product->seller?->name ?? 'Unknown'),
                ];
            });
        
        return Inertia::render('Admin/FeaturedProductAddView', [
            'availableProducts' => $availableProducts,
            'resourceNeo' => $this->resourceNeo,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
        ]);
        
        $count = 0;
        foreach ($validated['product_ids'] as $productId) {
            $product = Product::find($productId);
            if ($product && !$product->is_featured) {
                $product->update([
                    'is_featured' => true,
                    'featured_at' => now(),
                ]);
                $count++;
            }
        }
        
        \ActivityLog::add([
            'action' => 'featured_products',
            'module' => 'featuredProduct',
            'data_key' => "{$count} products"
        ]);
        
        return redirect()->route('featuredProduct.index')->with([
            'message' => "{$count} product(s) marked as featured",
            'msg_type' => 'success'
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        $product->update([
            'is_featured' => false,
            'featured_at' => null,
        ]);
        
        \ActivityLog::add([
            'action' => 'unfeatured_product',
            'module' => 'featuredProduct',
            'data_key' => $product->name
        ]);
        
        return redirect()->route('featuredProduct.index')->with([
            'message' => 'Product removed from featured',
            'msg_type' => 'success'
        ]);
    }
}
