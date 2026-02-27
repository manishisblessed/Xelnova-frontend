<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SellerBrandController extends Controller
{
    /**
     * Display brand management page
     */
    public function index()
    {
        $seller = Auth::user()->seller;
        
        if (!$seller) {
            return redirect()->route('seller.register')
                ->with('error', 'Please complete your seller registration first.');
        }

        $brands = $seller->brands()->latest()->get();

        return view('seller.brands.index', [
            'brands' => $brands,
            'seller' => $seller,
        ]);
    }

    /**
     * Store a new brand
     */
    public function store(Request $request)
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return back()->with('error', 'Seller profile not found.');
        }

        $validated = $request->validate([
            'brand_name' => 'required|string|max:255|unique:seller_brands,brand_name,NULL,id,seller_id,' . $seller->id,
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'proof_document' => 'required|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        $logoPath = null;
        $proofPath = null;

        // Upload logo if provided
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('seller-brands/logos');
        }

        // Upload proof document
        if ($request->hasFile('proof_document')) {
            $proofPath = $request->file('proof_document')->store('seller-brands/proofs');
        }

        SellerBrand::create([
            'seller_id' => $seller->id,
            'brand_name' => $validated['brand_name'],
            'description' => $validated['description'] ?? null,
            'logo_path' => $logoPath,
            'proof_document_path' => $proofPath,
            'approval_status' => 'pending',
        ]);

        return back()->with('message', 'Brand submitted successfully! It will be reviewed by our team.');
    }

    /**
     * Update a brand
     */
    public function update(Request $request, SellerBrand $brand)
    {
        $seller = Auth::user()->seller;

        if ($brand->seller_id !== $seller->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Only allow updates if brand is not approved
        if ($brand->isApproved()) {
            return back()->with('error', 'Cannot edit an approved brand. Please contact support if you need to make changes.');
        }

        $validated = $request->validate([
            'brand_name' => 'required|string|max:255|unique:seller_brands,brand_name,' . $brand->id . ',id,seller_id,' . $seller->id,
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'proof_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        $updateData = [
            'brand_name' => $validated['brand_name'],
            'description' => $validated['description'] ?? null,
        ];

        // Upload new logo if provided
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($brand->logo_path) {
                Storage::delete($brand->logo_path);
            }
            $updateData['logo_path'] = $request->file('logo')->store('seller-brands/logos');
        }

        // Upload new proof document if provided
        if ($request->hasFile('proof_document')) {
            // Delete old proof
            if ($brand->proof_document_path) {
                Storage::delete($brand->proof_document_path);
            }
            $updateData['proof_document_path'] = $request->file('proof_document')->store('seller-brands/proofs');
            // Reset approval status when proof is updated
            $updateData['approval_status'] = 'pending';
            $updateData['rejection_reason'] = null;
        }

        $brand->update($updateData);

        return back()->with('message', 'Brand updated successfully!');
    }

    /**
     * Delete a brand
     */
    public function destroy(SellerBrand $brand)
    {
        $seller = Auth::user()->seller;

        if ($brand->seller_id !== $seller->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Only allow deletion if brand is not approved
        if ($brand->isApproved()) {
            return back()->with('error', 'Cannot delete an approved brand. Please contact support.');
        }

        // Delete associated files
        if ($brand->logo_path) {
            Storage::delete($brand->logo_path);
        }
        if ($brand->proof_document_path) {
            Storage::delete($brand->proof_document_path);
        }

        $brand->delete();

        return back()->with('message', 'Brand deleted successfully!');
    }

    /**
     * Download proof document
     */
    public function downloadProof(SellerBrand $brand)
    {
        $seller = Auth::user()->seller;

        if ($brand->seller_id !== $seller->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        if (!$brand->proof_document_path || !Storage::exists($brand->proof_document_path)) {
            return back()->with('error', 'Proof document not found.');
        }

        return Storage::download($brand->proof_document_path);
    }
}
