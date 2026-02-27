<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BannerController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'banner',
        'resourceTitle' => 'Banners',
        'iconPath' => 'M21,3H3A2,2 0 0,0 1,5V19A2,2 0 0,0 3,21H21A2,2 0 0,0 23,19V5A2,2 0 0,0 21,3M21,19H3V5H21V19Z',
        'actions' => 'cud',
    ];

    public function __construct()
    {
        $this->middleware('can:banner_list', ['only' => ['index']]);
        $this->middleware('can:banner_create', ['only' => ['create', 'store']]);
        $this->middleware('can:banner_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:banner_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        // TODO: Implement when Banner model is created
        return Inertia::render('Admin/IndexView', [
            'resourceData' => ['data' => [], 'total' => 0],
            'resourceNeo' => $this->resourceNeo
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/AddEditView', [
            'formdata' => (object)[],
            'resourceNeo' => $this->resourceNeo
        ]);
    }

    public function store(Request $request)
    {
        // TODO: Implement banner creation with image upload
        return redirect()->route('banner.index')->with([
            'message' => 'Banner feature coming soon',
            'msg_type' => 'info'
        ]);
    }

    public function edit($id)
    {
        return Inertia::render('Admin/AddEditView', [
            'formdata' => (object)[],
            'resourceNeo' => $this->resourceNeo
        ]);
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('banner.index')->with([
            'message' => 'Banner updated',
            'msg_type' => 'success'
        ]);
    }

    public function destroy($id)
    {
        return redirect()->route('banner.index')->with([
            'message' => 'Banner deleted',
            'msg_type' => 'success'
        ]);
    }
}
