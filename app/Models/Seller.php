<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seller extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_type',
        'business_registration_number',
        'business_address',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'gst_number',
        'pan_number',
        'status',
        'verification_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'commission_rate',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'commission_rate' => 'decimal:2',
    ];

    /**
     * Get the user that owns the seller account
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the documents for the seller
     */
    public function documents()
    {
        return $this->hasMany(SellerDocument::class);
    }

    /**
     * Get the bank accounts for the seller
     */
    public function bankAccounts()
    {
        return $this->hasMany(SellerBankAccount::class);
    }

    /**
     * Get the brands for the seller
     */
    public function brands()
    {
        return $this->hasMany(SellerBrand::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(SellerPayoutRequest::class, 'seller_id', 'user_id');
    }


    /**
     * Get the user who approved the seller
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending sellers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved sellers
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include suspended sellers
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Scope a query to only include rejected sellers
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
