<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\LegalBasis;

class PrivacyComplianceService
{
    /**
     * Registra uma base legal no ecossistema.
     */
    public function registerLegalBasis(string $name, ?string $lawArticle = null, ?string $description = null): LegalBasis
    {
        return LegalBasis::create([
            'name' => $name,
            'law_article' => $lawArticle,
            'description' => $description,
        ]);
    }

    /**
     * Valida se existe a base legal informada.
     */
    public function hasLegalBasis(string $name): bool
    {
        return LegalBasis::where('name', $name)->exists();
    }
}
