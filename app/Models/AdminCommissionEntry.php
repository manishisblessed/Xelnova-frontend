<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminCommissionEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'sub_order_id',
        'commission_rate',
        'base_amount',
        'commission_amount',
        'entry_type',
        'notes',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function subOrder()
    {
        return $this->belongsTo(SubOrder::class);
    }
}
