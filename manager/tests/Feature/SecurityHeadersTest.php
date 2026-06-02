<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Route::get('/_test_security_headers_mgr', function () {
            return response('OK_MGR');
        })->middleware(SecurityHeadersMiddleware::class);
    }

    public function test_manager_contains_hsts_header()
    {
        $response = $this->get('/_test_security_headers_mgr');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    }

    public function test_manager_contains_x_frame_options_header()
    {
        $response = $this->get('/_test_security_headers_mgr');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_manager_contains_x_xss_protection_header()
    {
        $response = $this->get('/_test_security_headers_mgr');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_manager_contains_x_content_type_options_header()
    {
        $response = $this->get('/_test_security_headers_mgr');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_manager_contains_referrer_policy_header()
    {
        $response = $this->get('/_test_security_headers_mgr');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_manager_contains_permissions_policy_header()
    {
        $response = $this->get('/_test_security_headers_mgr');
        $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    public function test_manager_contains_content_security_policy_header()
    {
        $response = $this->get('/_test_security_headers_mgr');
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
    }
}
