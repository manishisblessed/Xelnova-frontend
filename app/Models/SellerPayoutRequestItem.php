<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerPayoutRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payout_request_id',
        'sub_order_id',
        'gross_amount',
        'commission_rate',
        'commission_amount',
        'net_amount',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function payoutRequest()
    {
        return $this->belongsTo(SellerPayoutRequest::class, 'payout_request_id');
    }

    public function subOrder()
    {
        return $this->belongsTo(SubOrder::class);
    }
}
