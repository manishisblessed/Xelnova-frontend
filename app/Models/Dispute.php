<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispute_number',
        'order_id',
        'sub_order_id',
        'user_id',
        'type',
        'subject',
        'description',
        'status',
        'priority',
        'resolution',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    protected $appends = [
        'order_number',
        'customer_name',
        'type_label',
        'status_label',
        'priority_label',
    ];

    /**
     * Dispute types with labels
     */
    public const TYPES = [
        'product_issue' => 'Product Issue',
        'delivery_issue' => 'Delivery Issue',
        'payment_issue' => 'Payment Issue',
        'seller_issue' => 'Seller Issue',
        'other' => 'Other',
    ];

    /**
     * Dispute statuses with labels
     */
    public const STATUSES = [
        'open' => 'Open',
        'under_review' => 'Under Review',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected',
        'closed' => 'Closed',
    ];

    /**
     * Priority levels with labels
     */
    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dispute) {
            if (empty($dispute->dispute_number)) {
                $dispute->dispute_number = self::generateDisputeNumber();
            }
        });
    }

    /**
     * Generate unique dispute number
     */
    public static function generateDisputeNumber(): string
    {
        $prefix = 'DSP';
        $date = now()->format('Ymd');
        $sequence = str_pad((self::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$date}{$sequence}";
    }

    /**
     * Get the order associated with the dispute
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the sub-order associated with the dispute (if any)
     */
    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    /**
     * Get the customer who raised the dispute
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who resolved the dispute
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope for open disputes
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for resolved disputes
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for pending disputes (not resolved or closed)
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['open', 'under_review']);
    }

    /**
     * Scope by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get order number from related order
     */
    public function getOrderNumberAttribute(): ?string
    {
        return $this->order?->order_number;
    }

    /**
     * Get customer name from related user
     */
    public function getCustomerNameAttribute(): ?string
    {
        return $this->user?->name;
    }

    /**
     * Get formatted type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get formatted status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get formatted priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'open' => 'bg-yellow-100 text-yellow-800',
            'under_review' => 'bg-blue-100 text-blue-800',
            'resolved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'closed' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get priority badge class for UI
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'bg-gray-100 text-gray-800',
            'medium' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-orange-100 text-orange-800',
            'urgent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Check if dispute can be resolved
     */
    public function canBeResolved(): bool
    {
        return in_array($this->status, ['open', 'under_review']);
    }

    /**
     * Mark dispute as resolved
     */
    public function resolve(string $resolution, int $resolvedBy): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution' => $resolution,
            'resolved_by' => $resolvedBy,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark dispute as under review
     */
    public function markUnderReview(): void
    {
        $this->update(['status' => 'under_review']);
    }

    /**
     * Reject the dispute
     */
    public function reject(string $reason, int $resolvedBy): void
    {
        $this->update([
            'status' => 'rejected',
            'resolution' => $reason,
            'resolved_by' => $resolvedBy,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Close the dispute
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }
}
