<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CustomerOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier',
        'type',
        'otp',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Maximum OTP verification attempts
     */
    const MAX_ATTEMPTS = 3;

    /**
     * OTP validity in minutes
     */
    const VALIDITY_MINUTES = 10;

    /**
     * Generate and save a new OTP
     */
    public static function generate(string $identifier, string $type = 'email'): self
    {
        // Delete any existing OTPs for this identifier
        self::where('identifier', $identifier)
            ->where('type', $type)
            ->delete();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'identifier' => $identifier,
            'type' => $type,
            'otp' => $otp,
            'attempts' => 0,
            'expires_at' => Carbon::now()->addMinutes(self::VALIDITY_MINUTES),
        ]);
    }

    /**
     * Verify OTP
     */
    public function verify(string $inputOtp): bool
    {
        // Check if already verified
        if ($this->verified_at) {
            return false;
        }

        // Check if expired
        if ($this->isExpired()) {
            return false;
        }

        // Check attempts
        if ($this->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        // Increment attempts
        $this->increment('attempts');

        // Check OTP
        if ($this->otp !== $inputOtp) {
            return false;
        }

        // Mark as verified
        $this->update(['verified_at' => Carbon::now()]);

        return true;
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return Carbon::now()->gt($this->expires_at);
    }

    /**
     * Check if max attempts reached
     */
    public function hasExhaustedAttempts(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Get remaining attempts
     */
    public function getRemainingAttemptsAttribute(): int
    {
        return max(0, self::MAX_ATTEMPTS - $this->attempts);
    }

    /**
     * Find valid OTP for identifier
     */
    public static function findValid(string $identifier, string $type = 'email'): ?self
    {
        return self::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->latest()
            ->first();
    }
}
