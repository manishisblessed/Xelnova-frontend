<?php

namespace Tests\Unit;

use App\Services\Sms\Providers\SmsFortiusProvider;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    public function test_it_resolves_smsfortius_provider(): void
    {
        config([
            'services.sms.default' => 'smsfortius',
            'services.sms.enabled' => true,
        ]);

        $service = new SmsService();
        $provider = $service->getProvider();

        $this->assertInstanceOf(SmsFortiusProvider::class, $provider);
    }

    public function test_it_sends_otp_with_smsfortius_query_params(): void
    {
        config([
            'services.sms.default' => 'smsfortius',
            'services.sms.enabled' => true,
            'services.smsfortius.base_url' => 'http://135.181.19.87/Login/V2/apikey.php',
            'services.smsfortius.apikey' => 'test-api-key',
            'services.smsfortius.senderid' => 'XELN',
            'services.smsfortius.country_code' => '91',
            'services.smsfortius.timeout' => 10,
        ]);

        Http::fake([
            '135.181.19.87/*' => Http::response('OK', 200),
        ]);

        $service = new SmsService();
        $sent = $service->sendOtp('9818660898', '123498', 10);

        $this->assertTrue($sent);

        Http::assertSent(function ($request) {
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);

            return $request->method() === 'GET'
                && str_contains($request->url(), '/Login/V2/apikey.php')
                && ($query['apikey'] ?? null) === 'test-api-key'
                && ($query['senderid'] ?? null) === 'XELN'
                && ($query['templateid'] ?? null) === SmsFortiusProvider::TEMPLATE_IDS[SmsFortiusProvider::TEMPLATE_LOGIN_OTP_VERIFICATION]
                && ($query['number'] ?? null) === '919818660898'
                && ($query['message'] ?? null) === 'XELNOVA: Your OTP is 123498. Please do not share this code with anyone. It is valid for 10 minutes.';
        });
    }
}
