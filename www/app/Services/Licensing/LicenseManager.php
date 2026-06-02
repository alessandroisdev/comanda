<?php

namespace App\Services\Licensing;

use App\Enums\LicenseStatusEnum;
use App\ValueObjects\LicenseKey;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

    public function getActiveLicense(): ?array
    {
        if (app()->environment('testing')) {
            return $this->loadFromFile();
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->loadFromFile();
        });
    }

    public function getStatus(): LicenseStatusEnum
    {
        $license = $this->getActiveLicense();
        if (! $license) {
            $this->logValidation('invalid', 'Licença ausente.');

            return LicenseStatusEnum::INVALID;
        }

        $status = $this->validator->validate($license);

        // Lógica de resiliência offline / grace period (7 dias de tolerância se expirar)
        if ($status === LicenseStatusEnum::EXPIRED && isset($license['expires_at'])) {
            $expiresAt = Carbon::parse($license['expires_at']);
            if (abs((int) Carbon::now()->diffInDays($expiresAt, false)) <= 7) {
                $this->logValidation('valid', 'Licença expirada operando sob período de carência offline (Grace Period).', $license['id'] ?? null);

                return LicenseStatusEnum::ACTIVE; // Tratado como ativo durante a carência
            }
        }

        $this->logValidation($status->value, 'Validação de licença executada localmente.', $license['id'] ?? null);

        return $status;
    }

    private function logValidation(string $status, string $details, ?int $licenseId = null): void
    {
        try {
            DB::table('license_validations')->insert([
                'license_id' => $licenseId,
                'status' => $status,
                'ip_address' => request()->ip(),
                'details' => $details,
                'created_at' => now(),
            ]);
        } catch (Exception $e) {
            // Silencia para não quebrar em caso de falha de banco isolada
        }
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
     * Retorna a licença ativa.
     */
    public function getLicenseData(): ?array
    {
        return $this->getActiveLicense();
    }

    /**
     * Retorna a quantidade de dias restantes para expirar a licença.
     * Retorna número negativo se já tiver expirado.
     */
    public function getDaysUntilExpiration(): ?int
    {
        $license = $this->getActiveLicense();
        if (! $license || ! isset($license['expires_at'])) {
            return null;
        }

        $expiresAt = Carbon::parse($license['expires_at']);

        return (int) Carbon::now()->diffInDays($expiresAt, false);
    }

    /**
     * Verifica se a licença está prestes a expirar (<= 15 dias de antecedência).
     */
    public function isExpiringSoon(): bool
    {
        $days = $this->getDaysUntilExpiration();
        if ($days === null) {
            return false;
        }

        return $days >= 0 && $days <= 15;
    }

    /**
     * Verifica se a licença está expirada mas operando em período de carência (Grace Period) de 7 dias.
     */
    public function isOperatingInGracePeriod(): bool
    {
        $license = $this->getActiveLicense();
        if (! $license || ! isset($license['expires_at'])) {
            return false;
        }

        $expiresAt = Carbon::parse($license['expires_at']);
        $now = Carbon::now();

        if ($now->greaterThan($expiresAt)) {
            return abs((int) $now->diffInDays($expiresAt, false)) <= 7;
        }

        return false;
    }

    /**
     * Retorna alertas de expiração para exibição na UI/Notificações.
     */
    public function getLicenseAlert(): ?array
    {
        $license = $this->getActiveLicense();
        if (! $license) {
            return [
                'type' => 'danger',
                'message' => 'Nenhuma licença comercial encontrada para esta instalação física. O acesso aos módulos está restrito.',
            ];
        }

        $status = $this->getStatus();
        if ($status === LicenseStatusEnum::INVALID) {
            return [
                'type' => 'danger',
                'message' => 'Licença comercial inválida ou corrompida. O acesso aos módulos está restrito.',
            ];
        }

        if ($status === LicenseStatusEnum::SUSPENDED) {
            return [
                'type' => 'danger',
                'message' => 'Licença comercial suspensa pela administração comercial. O acesso aos módulos está restrito.',
            ];
        }

        if ($status === LicenseStatusEnum::EXPIRED) {
            return [
                'type' => 'danger',
                'message' => 'Licença comercial expirada e período de carência esgotado. Módulos bloqueados.',
            ];
        }

        if ($this->isOperatingInGracePeriod()) {
            $days = 7 - (int) Carbon::now()->diffInDays(Carbon::parse($license['expires_at']));
            $days = max(1, $days);

            return [
                'type' => 'warning',
                'message' => "Licença comercial expirada! Operando sob período de carência (Grace Period). Restam {$days} dias antes do bloqueio total.",
            ];
        }

        if ($this->isExpiringSoon()) {
            $days = $this->getDaysUntilExpiration();

            return [
                'type' => 'info',
                'message' => "Sua licença comercial irá expirar em {$days} dias. Entre em contato para renovação.",
            ];
        }

        return null;
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
