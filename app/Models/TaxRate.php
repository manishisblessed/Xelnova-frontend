<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rate',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    /**
     * Scope a query to only include active tax rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
