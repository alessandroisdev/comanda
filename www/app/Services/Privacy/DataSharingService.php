<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\DataSharingRecord;

class DataSharingService
{
    /**
     * Registra e audita de forma persistente o compartilhamento de dados com terceiros.
     */
    public function logSharing(
        int $companyId,
        string $recipient,
        string $purpose,
        string $legalBasis,
        array $sharedFields,
        ?string $securityMeasures = null
    ): DataSharingRecord {
        return DataSharingRecord::create([
            'company_id' => $companyId,
            'recipient_name' => $recipient,
            'sharing_purpose' => $purpose,
            'legal_basis' => $legalBasis,
            'shared_data' => implode(', ', $sharedFields),
            'security_measures' => $securityMeasures,
        ]);
    }
}
