<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SubOrder extends Model
{
    use HasFactory;

    /**
     * Get the shipment associated with the sub-order.
     */
    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    protected $fillable = [
        'sub_order_number',
        'order_id',
        'seller_id',
        'subtotal',
        'shipping_charge',
        'tax',
        'total',
        'status',
        'tracking_number',
        'courier',
        'shipping_label_url',
        'confirmed_at',
        'packed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'refunded_at',
        'payout_at',
        'refund_amount',
        'refund_reason',
        'seller_notes',
        'admin_notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'payout_at' => 'datetime',
    ];

    /**
     * Generate sub-order number from parent order
     */
    public static function generateSubOrderNumber(Order $order, int $index): string
    {
        return $order->order_number . '-' . str_pad($index, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get the parent order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the seller (user)
     * Note: seller_id references users.id, not sellers.id
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the seller profile
     * Use this to access business_name and other seller-specific fields.
     */
    public function sellerProfile()
    {
        return $this->hasOneThrough(
            Seller::class,
            User::class,
            'id',           // Foreign key on users table
            'user_id',      // Foreign key on sellers table
            'seller_id',    // Local key on sub_orders table
            'id'            // Local key on users table
        );
    }

    /**
     * Get the order items for this sub-order
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(SellerLedgerEntry::class);
    }

    public function commissionEntries(): HasMany
    {
        return $this->hasMany(AdminCommissionEntry::class);
    }

    public function payoutRequestItem()
    {
        return $this->hasOne(SellerPayoutRequestItem::class);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'packed' => 'bg-indigo-100 text-indigo-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'out_for_delivery' => 'bg-indigo-100 text-indigo-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'returned' => 'bg-gray-100 text-gray-800',
            'refund_requested' => 'bg-orange-100 text-orange-800',
            'refunded' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get items count
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Check if sub-order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing']);
    }

    /**
     * Update status with timestamp
     */
    public function updateStatus(string $status): void
    {
        $timestamps = [
            'confirmed' => 'confirmed_at',
            'packed' => 'packed_at',
            'shipped' => 'shipped_at',
            'delivered' => 'delivered_at',
            'cancelled' => 'cancelled_at',
            'refunded' => 'refunded_at',
        ];

        $data = ['status' => $status];
        
        if (isset($timestamps[$status])) {
            $data[$timestamps[$status]] = now();
        }

        $this->update($data);
        
        // Update all items status
        $this->items()->update(['status' => $status]);
    }

    /**
     * Cancel sub-order and restore stock
     */
    public function cancel(string $reason = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        DB::transaction(function() use ($reason) {
            // Update status
            $this->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'seller_notes' => $reason,
            ]);

            // Update items status
            $this->items()->update(['status' => 'cancelled']);

            // Restore stock for each item
            foreach ($this->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }
        });

        return true;
    }
}
