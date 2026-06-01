<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\PrivacyAuditLog;
use App\Models\RetentionPolicy;
use Carbon\Carbon;

class DataRetentionService
{
    /**
     * Define ou atualiza uma política de retenção para uma categoria de dado pessoal.
     */
    public function setPolicy(string $category, int $months, ?string $obligation = null, string $disposalMethod = 'hard_delete'): RetentionPolicy
    {
        return RetentionPolicy::updateOrCreate(
            ['data_category' => $category],
            [
                'retention_months' => $months,
                'legal_obligation' => $obligation,
                'disposal_method' => $disposalMethod,
            ]
        );
    }

    /**
     * Aplica a retenção física expurgando registros fora do período de retenção.
     */
    public function applyRetention(): int
    {
        $policy = RetentionPolicy::where('data_category', 'logs')->first();
        if (! $policy) {
            return 0;
        }

        $limitDate = Carbon::now()->subMonths($policy->retention_months);

        // Expurga logs de auditoria obsoletos
        return PrivacyAuditLog::where('created_at', '<', $limitDate)->delete();
    }
}
