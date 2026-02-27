<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerPayoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'seller_id',
        'requested_amount',
        'approved_amount',
        'status',
        'requested_at',
        'reviewed_at',
        'paid_at',
        'reviewed_by',
        'payment_reference',
        'payment_method',
        'admin_notes',
        'seller_notes',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function sellerProfile()
    {
        return $this->hasOne(Seller::class, 'user_id', 'seller_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items()
    {
        return $this->hasMany(SellerPayoutRequestItem::class, 'payout_request_id');
    }
}
