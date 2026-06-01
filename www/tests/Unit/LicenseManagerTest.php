<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Licensing\LicenseManager;
use App\Services\Licensing\LicenseValidator;
use App\ValueObjects\LicenseKey;
use App\Enums\LicenseStatusEnum;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;

class LicenseManagerTest extends TestCase
{
    private LicenseManager $manager;
    private LicenseValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new LicenseValidator();
        $this->manager = new LicenseManager($this->validator);
        Cache::flush();
    }

    #[Test]
    public function it_returns_invalid_status_if_no_license_file_exists()
    {
        $path = storage_path('app/license.json');
        if (file_exists($path)) {
            unlink($path);
        }

        $this->manager->clearCache();
        $status = $this->manager->getStatus();
        $this->assertEquals(LicenseStatusEnum::INVALID, $status);
    }

    #[Test]
    public function it_fails_activation_if_key_data_is_corrupt()
    {
        $key = new LicenseKey(base64_encode('corrupt-non-json-data-that-is-long-enough-for-validation-criteria-to-pass-12345678'));
        
        $activated = $this->manager->activate($key);
        $this->assertFalse($activated);
    }
}
