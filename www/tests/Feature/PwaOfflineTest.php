<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PwaOfflineTest extends TestCase
{
    public function test_manifest_file_exists_in_public_directory()
    {
        $path = public_path('manifest.json');
        $this->assertTrue(File::exists($path));
    }

    public function test_manifest_returns_proper_json_headers()
    {
        $response = $this->get('/manifest.json');
        $response->assertStatus(200);
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type') ?? 'application/json');
    }

    public function test_manifest_has_valid_name()
    {
        $path = public_path('manifest.json');
        $data = json_decode(File::get($path), true);

        $this->assertArrayHasKey('name', $data);
        $this->assertNotEmpty($data['name']);
    }

    public function test_manifest_has_valid_short_name()
    {
        $path = public_path('manifest.json');
        $data = json_decode(File::get($path), true);

        $this->assertArrayHasKey('short_name', $data);
        $this->assertNotEmpty($data['short_name']);
    }

    public function test_manifest_has_valid_start_url()
    {
        $path = public_path('manifest.json');
        $data = json_decode(File::get($path), true);

        $this->assertArrayHasKey('start_url', $data);
        $this->assertEquals('/admin/tables', $data['start_url']);
    }

    public function test_manifest_has_valid_display_mode()
    {
        $path = public_path('manifest.json');
        $data = json_decode(File::get($path), true);

        $this->assertArrayHasKey('display', $data);
        $this->assertEquals('standalone', $data['display']);
    }

    public function test_manifest_contains_icons_array()
    {
        $path = public_path('manifest.json');
        $data = json_decode(File::get($path), true);

        $this->assertArrayHasKey('icons', $data);
        $this->assertIsArray($data['icons']);
        $this->assertNotEmpty($data['icons']);
    }

    public function test_service_worker_file_exists_in_public_directory()
    {
        $path = public_path('sw.js');
        $this->assertTrue(File::exists($path));
    }

    public function test_service_worker_returns_proper_javascript_headers()
    {
        $response = $this->get('/sw.js');
        $response->assertStatus(200);
        $this->assertStringContainsString('javascript', $response->headers->get('Content-Type') ?? 'application/javascript');
    }

    public function test_service_worker_contains_install_listener()
    {
        $path = public_path('sw.js');
        $content = File::get($path);

        $this->assertStringContainsString('install', $content);
    }

    public function test_service_worker_contains_fetch_listener()
    {
        $path = public_path('sw.js');
        $content = File::get($path);

        $this->assertStringContainsString('fetch', $content);
    }

    public function test_service_worker_contains_cache_names()
    {
        $path = public_path('sw.js');
        $content = File::get($path);

        $this->assertStringContainsString('cache', $content);
    }
}
