<?php

namespace App\Services\Sms\Contracts;

interface SmsProvider
{
    /**
     * Send OTP SMS.
     */
    public function sendOtp(string $phone, string $otp, int $validityMinutes): bool;

    /**
     * Send a message using a registered template key and payload.
     */
    public function sendTemplate(string $templateKey, string $phone, array $payload = []): bool;

    /**
     * Send plain SMS content.
     */
    public function sendMessage(string $phone, string $message): bool;
}
