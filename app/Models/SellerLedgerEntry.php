<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'sub_order_id',
        'payout_request_id',
        'entry_type',
        'direction',
        'amount',
        'balance_after',
        'idempotency_key',
        'description',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta' => 'array',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function subOrder()
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function payoutRequest()
    {
        return $this->belongsTo(SellerPayoutRequest::class, 'payout_request_id');
    }
}
