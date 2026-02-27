<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'account_holder_name',
        'account_number',
        'bank_name',
        'ifsc_code',
        'branch_name',
        'is_primary',
        'verification_status',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the seller that owns the bank account
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
