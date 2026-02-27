<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_order_id',
        'provider',        // delhivery, etc
        'service_type',    // Surface, Express, etc
        'awb_code',
        'provider_order_id',
        'status',
        'courier_name',
        'label_url',
        'manifest_url',
        'tracking_history',
        'meta_data'
    ];

    protected $casts = [
        'tracking_history' => 'array',
        'meta_data' => 'array',
    ];

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }
}
