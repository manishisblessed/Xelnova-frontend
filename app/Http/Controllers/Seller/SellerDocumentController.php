<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SellerDocumentController extends Controller
{
    /**
     * Display document management page
     */
    public function index()
    {
        $seller = Auth::user()->seller;
        
        if (!$seller) {
            return redirect()->route('seller.register')
                ->with('error', 'Please complete your seller registration first.');
        }

        $documents = $seller->documents;

        return view('seller.documents.index', [
            'documents' => $documents,
            'seller' => $seller,
        ]);
    }

    /**
     * Upload a new document
     */
    public function store(Request $request)
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return back()->with('error', 'Seller profile not found.');
        }

        $validated = $request->validate([
            'document_type' => 'required|in:pan_card,gst_certificate,business_registration,address_proof,bank_statement,other',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        
        // Store file in seller_documents directory
        $path = $file->store('seller_documents/' . $seller->id);

        SellerDocument::create([
            'seller_id' => $seller->id,
            'document_type' => $validated['document_type'],
            'document_path' => $path,
            'original_filename' => $originalName,
            'verification_status' => 'pending',
        ]);

        return back()->with('message', 'Document uploaded successfully!');
    }

    /**
     * Delete a document
     */
    public function destroy(SellerDocument $document)
    {
        $seller = Auth::user()->seller;

        if ($document->seller_id !== $seller->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Delete file from storage
        if (Storage::exists($document->document_path)) {
            Storage::delete($document->document_path);
        }

        $document->delete();

        return back()->with('message', 'Document deleted successfully!');
    }

    /**
     * Download a document
     */
    public function download(SellerDocument $document)
    {
        $seller = Auth::user()->seller;

        if ($document->seller_id !== $seller->id) {
            abort(403, 'Unauthorized action.');
        }

        if (!Storage::exists($document->document_path)) {
            abort(404, 'File not found.');
        }

        return Storage::download(
            $document->document_path,
            $document->original_filename
        );
    }
}
