<?php

namespace App\Http\View\Composers;

use App\Models\Category;
use Illuminate\View\View;

class MarketplaceComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        // Only load for marketplace views to avoid unnecessary queries on admin pages
        $viewName = $view->name();
        if (str_contains($viewName, 'marketplace') || str_contains($viewName, 'components.marketplace')) {
            // Load top-level categories for navigation
            // Using with() to prevent N+1 when accessing full_path
            $navCategories = Category::with('parent.parent.parent')
                ->active()
                ->topLevel()
                ->ordered()
                ->limit(10)
                ->get();
            
            $view->with('navCategories', $navCategories);
        }
    }
}
