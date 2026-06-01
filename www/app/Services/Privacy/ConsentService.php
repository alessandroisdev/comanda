<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\Consent;
use Carbon\Carbon;

class ConsentService
{
    /**
     * Registra o consentimento do titular.
     */
    public function grantConsent(
        int $companyId,
        string $subjectType,
        int $subjectId,
        string $subjectUuid,
        string $purpose,
        string $consentText,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): Consent {
        return Consent::create([
            'company_id' => $companyId,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_uuid' => $subjectUuid,
            'purpose' => $purpose,
            'consent_text' => $consentText,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => 'granted',
        ]);
    }

    /**
     * Revoga o consentimento.
     */
    public function revokeConsent(string $uuid): bool
    {
        $consent = Consent::where('uuid', $uuid)->first();
        if (! $consent) {
            return false;
        }

        return $consent->update([
            'status' => 'revoked',
            'revoked_at' => Carbon::now(),
        ]);
    }
}
