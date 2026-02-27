<?php

namespace App\Services\Sms;

use App\Services\Sms\Contracts\SmsProvider;
use App\Services\Sms\Providers\SmsFortiusProvider;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SmsService
{
    protected function isEnabled(): bool
    {
        return (bool) config('services.sms.enabled', true);
    }

    protected function skipWhenDisabled(string $operation, string $phone): bool
    {
        if ($this->isEnabled()) {
            return false;
        }

        Log::info('SMS sending skipped because service is disabled', [
            'operation' => $operation,
            'phone' => $phone,
        ]);

        return true;
    }

    public function getProvider(?string $providerName = null): SmsProvider
    {
        $providerName ??= config('services.sms.default', 'smsfortius');

        return match ($providerName) {
            'smsfortius' => new SmsFortiusProvider(),
            default => throw new InvalidArgumentException("SMS provider {$providerName} not supported"),
        };
    }

    public function sendOtp(string $phone, string $otp, int $validityMinutes, ?string $providerName = null): bool
    {
        if ($this->skipWhenDisabled('sendOtp', $phone)) {
            return true;
        }

        return $this->getProvider($providerName)->sendOtp($phone, $otp, $validityMinutes);
    }

    public function sendTemplate(string $templateKey, string $phone, array $payload = [], ?string $providerName = null): bool
    {
        if ($this->skipWhenDisabled('sendTemplate:' . $templateKey, $phone)) {
            return true;
        }

        return $this->getProvider($providerName)->sendTemplate($templateKey, $phone, $payload);
    }

    public function sendMessage(string $phone, string $message, ?string $providerName = null): bool
    {
        if ($this->skipWhenDisabled('sendMessage', $phone)) {
            return true;
        }

        return $this->getProvider($providerName)->sendMessage($phone, $message);
    }

    public function sendOrderPlaced(string $phone, string $orderId, string $amount, ?string $providerName = null): bool
    {
        return $this->sendTemplate(SmsFortiusProvider::TEMPLATE_ORDER_PLACED_CONFIRMATION, $phone, [
            'order_id' => $orderId,
            'amount' => $amount,
        ], $providerName);
    }

    public function sendPaymentSuccessful(string $phone, string $orderId, string $amount, ?string $providerName = null): bool
    {
        return $this->sendTemplate(SmsFortiusProvider::TEMPLATE_PAYMENT_SUCCESSFUL, $phone, [
            'order_id' => $orderId,
            'amount' => $amount,
        ], $providerName);
    }

    public function sendPaymentFailed(string $phone, string $orderId, string $paymentUrl, ?string $providerName = null): bool
    {
        return $this->sendTemplate(SmsFortiusProvider::TEMPLATE_PAYMENT_FAILED, $phone, [
            'order_id' => $orderId,
            'payment_url' => $paymentUrl,
        ], $providerName);
    }

    public function sendOrderProcessing(string $phone, string $orderId, ?string $providerName = null): bool
    {
        return $this->sendTemplate(SmsFortiusProvider::TEMPLATE_ORDER_PROCESSING, $phone, [
            'order_id' => $orderId,
        ], $providerName);
    }

    public function sendOrderPacked(string $phone, string $orderId, ?string $providerName = null): bool
    {
        return $this->sendTemplate(SmsFortiusProvider::TEMPLATE_ORDER_PACKED, $phone, [
            'order_id' => $orderId,
        ], $providerName);
    }

    public function sendOrderShipped(string $phone, string $orderId, string $courier, string $trackingUrl, ?string $providerName = null): bool
    {
        return $this->sendTemplate(SmsFortiusProvider::TEMPLATE_ORDER_SHIPPED_WITH_TRACKING, $phone, [
            'order_id' => $orderId,
            'courier' => $courier,
            'tracking_url' => $trackingUrl,
        ], $providerName);
    }
}
