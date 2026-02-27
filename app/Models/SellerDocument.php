<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'document_type',
        'document_path',
        'original_filename',
        'verification_status',
        'rejection_reason',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    protected $appends = [
        'document_url',
    ];

    /**
     * Get the seller that owns the document
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get the user who verified the document
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the document URL.
     */
    public function getDocumentUrlAttribute()
    {
        return file_url($this->document_path);
    }
}
