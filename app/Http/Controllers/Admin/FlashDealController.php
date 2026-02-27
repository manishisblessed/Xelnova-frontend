<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FlashDealController extends Controller
{
    protected $resourceNeo = [
        'resourceName' => 'flashDeal',
        'resourceTitle' => 'Flash Deals',
        'iconPath' => 'M7,2V13H10V22L17,10H13L17,2H7Z',
        'actions' => 'cud',
    ];

    public function __construct()
    {
        $this->middleware('can:flashDeal_list', ['only' => ['index']]);
        $this->middleware('can:flashDeal_create', ['only' => ['create', 'store']]);
        $this->middleware('can:flashDeal_edit', ['only' => ['edit', 'update']]);
        $this->middleware('can:flashDeal_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        // TODO: Implement when FlashDeal model is created
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
        // TODO: Implement flash deal creation
        return redirect()->route('flashDeal.index')->with([
            'message' => 'Flash deal feature coming soon',
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
        return redirect()->route('flashDeal.index')->with([
            'message' => 'Flash deal updated',
            'msg_type' => 'success'
        ]);
    }

    public function destroy($id)
    {
        return redirect()->route('flashDeal.index')->with([
            'message' => 'Flash deal deleted',
            'msg_type' => 'success'
        ]);
    }
}
