<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Licensing\LicenseManager;
use App\Services\Licensing\LicenseValidator;
use App\Services\Licensing\ModuleAccessService;
use App\ValueObjects\LicenseKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleAccessService $moduleAccessService,
        private readonly LicenseManager $licenseManager,
        private readonly LicenseValidator $licenseValidator
    ) {}

    /**
     * Exibe a tela administrativa de módulos e licenciamento com status de licença.
     */
    public function index(Request $request): View
    {
        // Política de acesso
        Gate::authorize('viewAnyModule', self::class);

        $modulesConfig = config('modules') ?? [];
        $modules = [];

        foreach ($modulesConfig as $key => $meta) {
            $modules[] = [
                'key' => $key,
                'name' => __($meta['name']),
                'description' => __($meta['description']),
                'version' => $meta['version'],
                'category' => $meta['category'],
                'core' => $meta['core'],
                'sellable' => $meta['sellable'],
                'dependencies' => $meta['dependencies'],
                'is_active' => $this->moduleAccessService->hasAccess($key),
            ];
        }

        $installationUuid = $this->licenseValidator->getLocalInstallationUuid();
        $licenseData = $this->licenseManager->getLicenseData();
        $licenseStatus = $this->licenseManager->getStatus();
        $licenseAlert = $this->licenseManager->getLicenseAlert();
        $daysUntilExpiration = $this->licenseManager->getDaysUntilExpiration();

        // Sugestões de configuração baseadas no .env
        $defaultManagerUrl = env('LICENSE_MANAGER_URL', 'http://localhost:8080');
        $defaultLicenseUuid = env('LICENSE_UUID', '');

        return view('admin.modules.index', compact(
            'modules',
            'installationUuid',
            'licenseData',
            'licenseStatus',
            'licenseAlert',
            'daysUntilExpiration',
            'defaultManagerUrl',
            'defaultLicenseUuid'
        ));
    }

    /**
     * Ativa uma licença online comunicando-se com o Manager Comercial.
     */
    public function activateOnline(Request $request): JsonResponse
    {
        Gate::authorize('viewAnyModule', self::class);

        $validator = Validator::make($request->all(), [
            'manager_url' => 'required|url',
            'license_uuid' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parâmetros de ativação inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $managerUrl = rtrim($request->input('manager_url'), '/');
        
        // Tradução automática de localhost/127.0.0.1 para 'nginx' para comunicação inter-container no Docker
        $parsedUrl = parse_url($managerUrl);
        if (isset($parsedUrl['host']) && in_array($parsedUrl['host'], ['localhost', '127.0.0.1'])) {
            $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
            $scheme = $parsedUrl['scheme'] ?? 'http';
            $path = $parsedUrl['path'] ?? '';
            $managerUrl = "{$scheme}://nginx{$port}{$path}";
        }

        $licenseUuid = $request->input('license_uuid');
        $installationUuid = $this->licenseValidator->getLocalInstallationUuid();

        try {
            $response = Http::timeout(10)->post($managerUrl . '/api/licenses/activate', [
                'license_uuid' => $licenseUuid,
                'installation_uuid' => $installationUuid,
                'hostname' => gethostname() ?: 'client-instance',
                'domain' => request()->getHost(),
                'ip_address' => request()->ip() ?: '127.0.0.1',
                'fingerprint' => md5((gethostname() ?: 'client') . (request()->ip() ?: '127.0.0.1') . PHP_OS),
            ]);

            if ($response->failed()) {
                $errorData = $response->json();
                $message = $errorData['message'] ?? 'Falha na resposta do servidor de licenças.';
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao comunicar com o Manager: ' . $message,
                ], 400);
            }

            $responseData = $response->json();

            if (empty($responseData['activation_key'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chave de ativação não retornada pelo Manager.',
                ], 400);
            }

            // Ativar a licença usando o LicenseManager
            $activated = $this->licenseManager->activate(
                new LicenseKey($responseData['activation_key'])
            );

            if (! $activated) {
                return response()->json([
                    'success' => false,
                    'message' => 'A chave recebida é inválida ou incompatível com esta instalação física.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Licença online ativada e módulos desbloqueados com sucesso!',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de conexão com o Manager: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ativa uma licença offline gravando a chave fornecida.
     */
    public function activateOffline(Request $request): JsonResponse
    {
        Gate::authorize('viewAnyModule', self::class);

        $validator = Validator::make($request->all(), [
            'activation_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Chave de ativação é obrigatória.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $keyString = $request->input('activation_key');

        // Se o usuário colou um JSON completo contendo 'activation_key', extraímos
        if (str_starts_with(trim($keyString), '{')) {
            $json = json_decode($keyString, true);
            if (isset($json['activation_key'])) {
                $keyString = $json['activation_key'];
            }
        }

        try {
            $activated = $this->licenseManager->activate(
                new LicenseKey($keyString)
            );

            if (! $activated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chave de ativação inválida, expirada ou incompatível com esta instalação física.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Licença offline ativada e módulos desbloqueados com sucesso!',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Falha ao processar a chave: ' . $e->getMessage(),
            ], 400);
        }
    }
}
