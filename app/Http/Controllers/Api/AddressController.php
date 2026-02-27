<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Get all addresses for the authenticated user
     */
    public function index()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to view addresses',
            ], 401);
        }

        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    /**
     * Store a new address
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to add an address',
            ], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'country' => 'nullable|string|max:100',
            'type' => 'required|in:home,office,other',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($request->is_default) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        $address = Auth::user()->addresses()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully',
            'data' => $address,
        ], 201);
    }

    /**
     * Update an address
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to update address',
            ], 401);
        }

        $address = Auth::user()->addresses()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'country' => 'nullable|string|max:100',
            'type' => 'required|in:home,office,other',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($request->is_default) {
            Auth::user()->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => $address,
        ]);
    }

    /**
     * Delete an address
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to delete address',
            ], 401);
        }

        $address = Auth::user()->addresses()->findOrFail($id);
        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }

    /**
     * Set an address as default
     */
    public function setDefault($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to set default address',
            ], 401);
        }

        $address = Auth::user()->addresses()->findOrFail($id);

        // Unset all other defaults
        Auth::user()->addresses()->update(['is_default' => false]);

        // Set this as default
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated',
            'data' => $address,
        ]);
    }
}
