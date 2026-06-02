<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Garante uma rota simples para teste
        Route::get('/_test_security_headers', function () {
            return response('OK');
        })->middleware(\App\Http\Middleware\SecurityHeadersMiddleware::class);
    }

    public function test_it_contains_hsts_header()
    {
        $response = $this->get('/_test_security_headers');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    }

    public function test_it_contains_x_frame_options_header()
    {
        $response = $this->get('/_test_security_headers');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_it_contains_x_xss_protection_header()
    {
        $response = $this->get('/_test_security_headers');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_it_contains_x_content_type_options_header()
    {
        $response = $this->get('/_test_security_headers');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_it_contains_referrer_policy_header()
    {
        $response = $this->get('/_test_security_headers');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_it_contains_permissions_policy_header()
    {
        $response = $this->get('/_test_security_headers');
        $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    public function test_it_contains_content_security_policy_header()
    {
        $response = $this->get('/_test_security_headers');
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
    }

    public function test_it_generates_and_returns_correlation_id_header()
    {
        $response = $this->get('/_test_security_headers');
        $response->assertHeaderHas('X-Correlation-ID');
        $this->assertNotEmpty($response->headers->get('X-Correlation-ID'));
    }

    public function test_it_propagates_existing_correlation_id_header()
    {
        $testUuid = '12345678-abcd-1234-abcd-1234567890ab';
        $response = $this->withHeaders([
            'X-Correlation-ID' => $testUuid
        ])->get('/_test_security_headers');

        $response->assertHeader('X-Correlation-ID', $testUuid);
    }

    public function test_it_registers_correlation_and_request_ids_in_app_container()
    {
        $this->get('/_test_security_headers');

        $this->assertTrue(app()->bound('correlation_id'));
        $this->assertTrue(app()->bound('request_id'));
        $this->assertNotEmpty(app('correlation_id'));
        $this->assertNotEmpty(app('request_id'));
    }
}
