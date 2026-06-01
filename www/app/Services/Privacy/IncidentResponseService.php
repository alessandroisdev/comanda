<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\PrivacyIncident;

class IncidentResponseService
{
    /**
     * Registra e audita um incidente de privacidade e segurança física.
     */
    public function logIncident(
        int $companyId,
        string $type,
        string $severity,
        string $affectedData,
        string $description
    ): PrivacyIncident {
        return PrivacyIncident::create([
            'company_id' => $companyId,
            'incident_type' => $type,
            'severity' => $severity,
            'affected_data' => $affectedData,
            'description' => $description,
            'status' => 'open',
        ]);
    }

    /**
     * Atualiza o estado das providências adotadas para mitigar ou resolver o incidente.
     */
    public function updateMitigation(string $uuid, string $measures, string $status = 'resolved', bool $anpd = false, bool $subjects = false): bool
    {
        $incident = PrivacyIncident::where('uuid', $uuid)->first();
        if (! $incident) {
            return false;
        }

        return $incident->update([
            'measures_taken' => $measures,
            'status' => $status,
            'anpd_notified' => $anpd,
            'subjects_notified' => $subjects,
        ]);
    }
}
