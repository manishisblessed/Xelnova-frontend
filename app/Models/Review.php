<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_item_id',
        'rating',
        'title',
        'review',
        'images',
        'is_verified_purchase',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'images' => 'array',
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
    ];

    /**
     * Get the user who wrote the review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product being reviewed
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order item (if verified purchase)
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Scope for approved reviews
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for verified purchases
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Get star display (for blade templates)
     */
    public function getStarsHtmlAttribute(): string
    {
        $full = $this->rating;
        $empty = 5 - $full;

        $stars = str_repeat('★', $full) . str_repeat('☆', $empty);

        return $stars;
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M d, Y');
    }
}
