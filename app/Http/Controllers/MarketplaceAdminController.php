<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MarketplaceAdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.marketplace.dashboard');
    }

    public function sellers()
    {
        return view('admin.marketplace.sellers.index');
    }

    public function products()
    {
        return view('admin.marketplace.products.index');
    }
}
