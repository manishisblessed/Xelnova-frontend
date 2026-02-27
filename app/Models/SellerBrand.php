<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'brand_name',
        'description',
        'logo_path',
        'proof_document_path',
        'approval_status',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'seller_business_name',
        'approval_status_label',
        'logo_url',
        'proof_url',
    ];


    /**
     * Get the seller that owns the brand
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Check if brand is approved
     */
    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check if brand is pending
     */
    public function isPending()
    {
        return $this->approval_status === 'pending';
    }

    /**
     * Check if brand is rejected
     */
    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }

    /**
     * Get seller business name for table display
     */
    public function getSellerBusinessNameAttribute()
    {
        return $this->seller ? $this->seller->business_name : 'N/A';
    }

    /**
     * Get approval status label for table display
     */
    public function getApprovalStatusLabelAttribute()
    {
        return ucfirst($this->approval_status);
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute()
    {
        return file_url($this->logo_path);
    }

    /**
     * Get the proof document URL.
     */
    public function getProofUrlAttribute()
    {
        return file_url($this->proof_document_path);
    }
}


