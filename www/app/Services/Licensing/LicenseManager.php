<?php

namespace App\Services\Licensing;

use App\Enums\LicenseStatusEnum;
use App\ValueObjects\LicenseKey;
use Exception;
use Illuminate\Support\Facades\Cache;

class LicenseManager
{
    private LicenseValidator $validator;

    private string $licenseFilePath;

    private const CACHE_KEY = 'license:active_metadata';

    private const CACHE_TTL = 86400; // 24 horas (em segundos)

    public function __construct(LicenseValidator $validator)
    {
        $this->validator = $validator;
        $this->licenseFilePath = storage_path('app/license.json');
    }

    /**
     * Retorna a licença ativa cacheada no Redis ou a carrega do arquivo físico.
     */
    public function getActiveLicense(): ?array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->loadFromFile();
        });
    }

    /**
     * Retorna o status operacional atual da licença instalada.
     */
    public function getStatus(): LicenseStatusEnum
    {
        $license = $this->getActiveLicense();
        if (! $license) {
            return LicenseStatusEnum::INVALID;
        }

        return $this->validator->validate($license);
    }

    /**
     * Salva e ativa uma nova licença no sistema principal.
     */
    public function activate(LicenseKey $key): bool
    {
        try {
            $licenseData = json_decode(base64_decode($key->value()), true);

            if (! $licenseData || ! is_array($licenseData)) {
                throw new Exception(__('licenses.errors.invalid_format'));
            }

            // Validar antes de persistir fisicamente
            $status = $this->validator->validate($licenseData);
            if (! $status->isActive()) {
                throw new Exception(__('licenses.errors.validation_failed'));
            }

            // Persistir localmente de forma segura
            if (! is_dir(dirname($this->licenseFilePath))) {
                mkdir(dirname($this->licenseFilePath), 0755, true);
            }

            file_put_contents($this->licenseFilePath, json_encode($licenseData, JSON_PRETTY_PRINT));

            // Limpar cache para forçar recarregamento na próxima consulta
            $this->clearCache();

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Limpa o cache de validação no Redis.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Carrega a licença diretamente do arquivo físico do storage.
     */
    private function loadFromFile(): ?array
    {
        if (! file_exists($this->licenseFilePath)) {
            return null;
        }

        $content = file_get_contents($this->licenseFilePath);
        $data = json_decode($content, true);

        return is_array($data) ? $data : null;
    }
}
