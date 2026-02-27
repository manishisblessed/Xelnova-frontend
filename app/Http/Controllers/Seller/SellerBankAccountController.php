<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerBankAccountController extends Controller
{
    /**
     * Display bank account management page
     */
    public function index()
    {
        $seller = Auth::user()->seller;
        
        if (!$seller) {
            return redirect()->route('seller.register')
                ->with('error', 'Please complete your seller registration first.');
        }

        $bankAccounts = $seller->bankAccounts;

        return view('seller.bank-accounts.index', [
            'bankAccounts' => $bankAccounts,
            'seller' => $seller,
        ]);
    }

    /**
     * Store a new bank account
     */
    public function store(Request $request)
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return back()->with('error', 'Seller profile not found.');
        }

        $validated = $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:11',
            'branch_name' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
        ]);

        // If this is set as primary, unset other primary accounts
        if ($request->boolean('is_primary')) {
            SellerBankAccount::where('seller_id', $seller->id)
                ->update(['is_primary' => false]);
        }

        SellerBankAccount::create([
            'seller_id' => $seller->id,
            'account_holder_name' => $validated['account_holder_name'],
            'account_number' => $validated['account_number'],
            'bank_name' => $validated['bank_name'],
            'ifsc_code' => $validated['ifsc_code'],
            'branch_name' => $validated['branch_name'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false,
            'verification_status' => 'pending',
        ]);

        return back()->with('message', 'Bank account added successfully!');
    }

    /**
     * Update a bank account
     */
    public function update(Request $request, SellerBankAccount $bankAccount)
    {
        $seller = Auth::user()->seller;

        if ($bankAccount->seller_id !== $seller->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:11',
            'branch_name' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
        ]);

        // If this is set as primary, unset other primary accounts
        if ($request->boolean('is_primary') && !$bankAccount->is_primary) {
            SellerBankAccount::where('seller_id', $seller->id)
                ->where('id', '!=', $bankAccount->id)
                ->update(['is_primary' => false]);
        }

        $bankAccount->update($validated);

        return back()->with('message', 'Bank account updated successfully!');
    }

    /**
     * Delete a bank account
     */
    public function destroy(SellerBankAccount $bankAccount)
    {
        $seller = Auth::user()->seller;

        if ($bankAccount->seller_id !== $seller->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        $bankAccount->delete();

        return back()->with('message', 'Bank account deleted successfully!');
    }

    /**
     * Set a bank account as primary
     */
    public function setPrimary(SellerBankAccount $bankAccount)
    {
        $seller = Auth::user()->seller;

        if ($bankAccount->seller_id !== $seller->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Unset all primary accounts
        SellerBankAccount::where('seller_id', $seller->id)
            ->update(['is_primary' => false]);

        // Set this as primary
        $bankAccount->update(['is_primary' => true]);

        return back()->with('message', 'Primary bank account updated!');
    }
}
