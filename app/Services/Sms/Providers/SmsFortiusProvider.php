<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\Contracts\SmsProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsFortiusProvider implements SmsProvider
{
    public const TEMPLATE_LOGIN_OTP_VERIFICATION = 'login_otp_verification';
    public const TEMPLATE_ORDER_PLACED_CONFIRMATION = 'order_placed_confirmation';
    public const TEMPLATE_PAYMENT_SUCCESSFUL = 'payment_successful';
    public const TEMPLATE_PAYMENT_FAILED = 'payment_failed';
    public const TEMPLATE_ORDER_PROCESSING = 'order_processing';
    public const TEMPLATE_ORDER_PACKED = 'order_packed';
    public const TEMPLATE_ORDER_SHIPPED_WITH_TRACKING = 'order_shipped_with_tracking';

    public const TEMPLATE_IDS = [
        self::TEMPLATE_LOGIN_OTP_VERIFICATION => '1707177018835749938',
        self::TEMPLATE_ORDER_PLACED_CONFIRMATION => '1707177018842418611',
        self::TEMPLATE_PAYMENT_SUCCESSFUL => '1707177018832478298',
        self::TEMPLATE_PAYMENT_FAILED => '1707177018828455688',
        self::TEMPLATE_ORDER_PROCESSING => '1707177018824258746',
        self::TEMPLATE_ORDER_PACKED => '1707177018858944064',
        self::TEMPLATE_ORDER_SHIPPED_WITH_TRACKING => '1707177018854193139',
    ];

    protected const MESSAGE_TEMPLATES = [
        self::TEMPLATE_LOGIN_OTP_VERIFICATION =>
            'XELNOVA: Your OTP is {otp}. Please do not share this code with anyone. It is valid for {validity_minutes} minutes.',
        self::TEMPLATE_ORDER_PLACED_CONFIRMATION =>
            'XELNOVA: Thank you for your order! Order ID {order_id} placed successfully. Amount Rs.{amount}. You\'ll be notified once it is shipped.',
        self::TEMPLATE_PAYMENT_SUCCESSFUL =>
            'XELNOVA: Payment of Rs.{amount} received successfully for Order {order_id}. Thank you for shopping with us.',
        self::TEMPLATE_PAYMENT_FAILED =>
            'XELNOVA: Payment for Order {order_id} was unsuccessful. Please retry to avoid cancellation. Pay now: {payment_url}',
        self::TEMPLATE_ORDER_PROCESSING =>
            'XELNOVA: Your Order {order_id} is being processed and prepared for dispatch. You will receive shipping updates shortly.',
        self::TEMPLATE_ORDER_PACKED =>
            'XELNOVA: Good news! Your Order {order_id} is packed and ready for dispatch.',
        self::TEMPLATE_ORDER_SHIPPED_WITH_TRACKING =>
            'XELNOVA: Your Order {order_id} has been shipped via {courier}. Track your shipment here: {tracking_url}',
    ];

    protected string $baseUrl;
    protected string $apiKey;
    protected string $senderId;
    protected string $countryCode;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.smsfortius.base_url', '');
        $this->apiKey = (string) config('services.smsfortius.apikey', '');
        $this->senderId = (string) config('services.smsfortius.senderid', '');
        $this->countryCode = (string) config('services.smsfortius.country_code', '91');
        $this->timeout = (int) config('services.smsfortius.timeout', 10);
    }

    public function sendOtp(string $phone, string $otp, int $validityMinutes): bool
    {
        return $this->sendTemplate(self::TEMPLATE_LOGIN_OTP_VERIFICATION, $phone, [
            'otp' => $otp,
            'validity_minutes' => (string) $validityMinutes,
        ]);
    }

    public function sendTemplate(string $templateKey, string $phone, array $payload = []): bool
    {
        if (!isset(self::TEMPLATE_IDS[$templateKey], self::MESSAGE_TEMPLATES[$templateKey])) {
            Log::error('Unknown SMS template key', [
                'provider' => 'smsfortius',
                'template_key' => $templateKey,
            ]);
            return false;
        }

        $templateId = self::TEMPLATE_IDS[$templateKey];
        $messageTemplate = self::MESSAGE_TEMPLATES[$templateKey];
        $message = $this->renderTemplate($templateKey, $messageTemplate, $payload);

        if ($message === null) {
            return false;
        }

        return $this->dispatchRequest($phone, $message, $templateId);
    }

    public function sendMessage(string $phone, string $message): bool
    {
        // Plain messaging support is intentionally left without a template id.
        // Use sendTemplate(...) for all DLT-approved transactional messages.
        return $this->dispatchRequest($phone, $message, null);
    }

    protected function dispatchRequest(string $phone, string $message, ?string $templateId): bool
    {
        if ($this->baseUrl === '' || $this->apiKey === '' || $this->senderId === '') {
            Log::error('SMS provider configuration missing', [
                'provider' => 'smsfortius',
                'has_base_url' => $this->baseUrl !== '',
                'has_api_key' => $this->apiKey !== '',
                'has_sender_id' => $this->senderId !== '',
            ]);

            return false;
        }

        $numbers = $this->normalizePhones($phone);

        $query = [
            'apikey' => $this->apiKey,
            'senderid' => $this->senderId,
            'number' => $numbers,
            'message' => $message,
        ];

        if (!empty($templateId)) {
            $query['templateid'] = $templateId;
        }

        try {
            $response = Http::timeout($this->timeout)->retry(1, 200)->get($this->baseUrl, $query);
            $body = strtolower(trim($response->body()));

            if (!$response->successful() || $this->looksLikeGatewayError($body)) {
                Log::error('SMS delivery failed', [
                    'provider' => 'smsfortius',
                    'status' => $response->status(),
                    'phone' => $this->maskPhoneList($numbers),
                    'response' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('SMS gateway request exception', [
                'provider' => 'smsfortius',
                'phone' => $this->maskPhoneList($numbers),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function renderTemplate(string $templateKey, string $template, array $payload): ?string
    {
        preg_match_all('/\{([a-z0-9_]+)\}/i', $template, $matches);
        $requiredKeys = array_unique($matches[1] ?? []);

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $payload)) {
                Log::error('Missing SMS template payload key', [
                    'provider' => 'smsfortius',
                    'template_key' => $templateKey,
                    'missing_key' => $key,
                ]);

                return null;
            }
        }

        foreach ($payload as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        return $template;
    }

    protected function normalizePhones(string $phoneList): string
    {
        $phones = explode(',', $phoneList);
        $normalized = [];

        foreach ($phones as $phone) {
            $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';
            $countryCode = preg_replace('/\D+/', '', $this->countryCode) ?? '91';

            if (strlen($digits) === 10) {
                $normalized[] = $countryCode . $digits;
                continue;
            }

            if ($countryCode !== '' && str_starts_with($digits, $countryCode)) {
                $normalized[] = $digits;
                continue;
            }

            if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
                $normalized[] = $countryCode . substr($digits, 1);
                continue;
            }

            if ($digits !== '') {
                $normalized[] = $digits;
            }
        }

        return implode(',', $normalized);
    }

    protected function maskPhoneList(string $phoneList): string
    {
        $phones = array_filter(explode(',', $phoneList));
        $masked = array_map(fn (string $phone) => $this->maskPhone(trim($phone)), $phones);

        return implode(',', $masked);
    }

    protected function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($phone, -4);
    }

    protected function looksLikeGatewayError(string $body): bool
    {
        if ($body === '') {
            return true;
        }

        return str_contains($body, 'error')
            || str_contains($body, 'invalid')
            || str_contains($body, 'failed')
            || str_contains($body, 'not found')
            || str_contains($body, 'insufficient');
    }
}
