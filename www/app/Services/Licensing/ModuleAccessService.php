<?php

namespace App\Services\Licensing;

class ModuleAccessService
{
    private LicenseManager $licenseManager;

    private array $modulesConfig;

    public function __construct(LicenseManager $licenseManager)
    {
        $this->licenseManager = $licenseManager;
        // Carrega o arquivo oficial de configuração
        $this->modulesConfig = config('modules') ?? [];
    }

    /**
     * Verifica se o módulo específico está ativo e disponível para uso na instalação.
     *
     * @param  string  $moduleKey  Chave do módulo (ex: pdv, waiter, kitchen)
     */
    public function hasAccess(string $moduleKey): bool
    {
        // 1. Módulos Core estão sempre liberados para fins de governança e recuperação do sistema
        if (isset($this->modulesConfig[$moduleKey]) && $this->modulesConfig[$moduleKey]['core'] === true) {
            return true;
        }

        // 2. Obter status da licença geral
        $licenseStatus = $this->licenseManager->getStatus();
        if (! $licenseStatus->isActive()) {
            return false;
        }

        // 3. Obter metadados da licença ativa
        $license = $this->licenseManager->getActiveLicense();
        if (! $license || empty($license['modules']) || ! is_array($license['modules'])) {
            return false;
        }

        // 4. Verificar se o módulo está explicitamente listado e ativo na licença
        if (! in_array($moduleKey, $license['modules'])) {
            return false;
        }

        // 5. Verificar dependências do módulo na configuração oficial
        return $this->validateDependencies($moduleKey, $license['modules']);
    }

    /**
     * Valida de forma recursiva se todas as dependências declaradas estão ativas na licença.
     */
    private function validateDependencies(string $moduleKey, array $licensedModules): bool
    {
        if (! isset($this->modulesConfig[$moduleKey])) {
            return false;
        }

        $dependencies = $this->modulesConfig[$moduleKey]['dependencies'] ?? [];

        foreach ($dependencies as $depKey) {
            // Se a dependência é core, está sempre ativa. Senão, deve estar na licença
            $isCore = $this->modulesConfig[$depKey]['core'] ?? false;

            if (! $isCore && ! in_array($depKey, $licensedModules)) {
                return false;
            }

            // Validar dependências da dependência recursivamente
            if (! $this->validateDependencies($depKey, $licensedModules)) {
                return false;
            }
        }

        return true;
    }
}
