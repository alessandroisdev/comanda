<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseAuditLog;
use App\Models\LicenseInstallation;
use App\Models\Module;
use App\Services\Licensing\LicenseIssuerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LicenseApiController extends Controller
{
    private LicenseIssuerService $licenseIssuer;

    public function __construct(LicenseIssuerService $licenseIssuer)
    {
        $this->licenseIssuer = $licenseIssuer;
    }

    /**
     * POST /api/licenses/generate
     * Emite uma nova licença no Manager.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'client_name' => 'required|string|max:150',
            'client_email' => 'required|email|max:150',
            'client_document' => 'required|string|max:30',
            'plan_name' => 'required|string|max:100',
            'type' => 'required|string|in:trial,subscription,perpetual,developer,internal',
            'modules' => 'required|array',
            'modules.*' => 'string|exists:modules,code',
            'expires_at' => 'nullable|date',
        ]);

        $result = DB::transaction(function () use ($request) {
            /** @var License $license */
            $license = License::create([
                'client_name' => $request->json('client_name'),
                'client_email' => $request->json('client_email'),
                'client_document' => $request->json('client_document'),
                'plan_name' => $request->json('plan_name'),
                'type' => $request->json('type'),
                'status' => $request->json('type') === 'trial' ? 'trial' : 'active',
                'issued_at' => Carbon::now(),
                'expires_at' => $request->json('expires_at') ? Carbon::parse($request->json('expires_at')) : Carbon::now()->addYear(),
            ]);

            // Vincula os módulos da licença
            $moduleIds = Module::whereIn('code', $request->json('modules'))->pluck('id');
            $license->modules()->sync($moduleIds);

            // Gera log de auditoria
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'action' => 'issue',
                'details' => [
                    'type' => $license->type,
                    'plan_name' => $license->plan_name,
                    'modules' => $request->json('modules'),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $license->load('modules');
        });

        return response()->json($result, 201);
    }

    /**
     * POST /api/licenses/activate
     * Ativa uma licença vinculando-a a uma instalação física local por UUID e fingerprint.
     */
    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'license_uuid' => 'required|uuid|exists:licenses,uuid',
            'installation_uuid' => 'required|uuid',
            'hostname' => 'required|string|max:150',
            'domain' => 'nullable|string|max:150',
            'ip_address' => 'required|ip',
            'fingerprint' => 'required|string|max:255',
        ]);

        $licenseUuid = $request->json('license_uuid');
        $installationUuid = $request->json('installation_uuid');
        $hostname = $request->json('hostname');
        $domain = $request->json('domain');
        $ipAddress = $request->json('ip_address');
        $fingerprint = $request->json('fingerprint');

        $result = DB::transaction(function () use (
            $licenseUuid, $installationUuid, $hostname, $domain, $ipAddress, $fingerprint
        ) {
            /** @var License $license */
            $license = License::where('uuid', $licenseUuid)->firstOrFail();

            if (in_array($license->status, ['suspended', 'cancelled', 'blocked'])) {
                return ['success' => false, 'message' => "Licença não elegível para ativação: status {$license->status}."];
            }

            // 1. Registra ou atualiza a instalação no Manager
            /** @var LicenseInstallation $installation */
            $installation = LicenseInstallation::where('uuid', $installationUuid)->first();
            if (! $installation) {
                $installation = LicenseInstallation::create([
                    'uuid' => $installationUuid,
                    'hostname' => $hostname,
                    'domain' => $domain,
                    'ip_address' => $ipAddress,
                    'fingerprint' => $fingerprint,
                    'status' => 'active',
                ]);
            } else {
                if ($installation->status === 'blocked') {
                    return ['success' => false, 'message' => 'Esta instalação física está permanentemente bloqueada.'];
                }
                $installation->update([
                    'hostname' => $hostname,
                    'domain' => $domain,
                    'ip_address' => $ipAddress,
                    'fingerprint' => $fingerprint,
                ]);
            }

            // 2. Registra o vínculo ativo de ativação
            LicenseActivation::updateOrCreate(
                [
                    'license_id' => $license->id,
                    'installation_uuid' => $installationUuid,
                ],
                [
                    'hostname' => $hostname,
                    'domain' => $domain,
                    'ip_address' => $ipAddress,
                    'fingerprint' => $fingerprint,
                    'status' => 'active',
                    'activated_at' => Carbon::now(),
                ]
            );

            // 3. Dispara a assinatura criptográfica RSA e gera a chave de ativação base64
            $modulesKeys = $license->modules()->pluck('code')->toArray();
            $activationKey = $this->licenseIssuer->issue($license, $modulesKeys, $installationUuid);

            // Log de auditoria
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'installation_uuid' => $installationUuid,
                'action' => 'activate',
                'details' => [
                    'hostname' => $hostname,
                    'fingerprint' => $fingerprint,
                    'ip_address' => $ipAddress,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return [
                'success' => true,
                'license_uuid' => $license->uuid,
                'activation_key' => $activationKey,
                'status' => $license->status,
                'expires_at' => $license->expires_at ? $license->expires_at->toIso8601String() : null,
            ];
        });

        if (isset($result['success']) && ! $result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result, 200);
    }

    /**
     * POST /api/licenses/renew
     * Renova e reemite uma licença ativa.
     */
    public function renew(Request $request): JsonResponse
    {
        $request->validate([
            'license_uuid' => 'required|uuid|exists:licenses,uuid',
            'expires_at' => 'required|date',
            'modules' => 'nullable|array',
            'modules.*' => 'string|exists:modules,code',
        ]);

        $licenseUuid = $request->json('license_uuid');
        $expiresAt = Carbon::parse($request->json('expires_at'));
        $newModules = $request->json('modules');

        $result = DB::transaction(function () use ($licenseUuid, $expiresAt, $newModules) {
            /** @var License $license */
            $license = License::where('uuid', $licenseUuid)->firstOrFail();

            $license->update([
                'expires_at' => $expiresAt,
                'status' => $license->type === 'trial' ? 'trial' : 'active',
            ]);

            if ($newModules) {
                $moduleIds = Module::whereIn('code', $newModules)->pluck('id');
                $license->modules()->sync($moduleIds);
            }

            // Recarrega os módulos atuais
            $modulesKeys = $license->modules()->pluck('code')->toArray();

            // Busca a última ativação para obter a installation_uuid
            $activation = $license->activations()->where('status', 'active')->first();
            $installationUuid = $activation ? $activation->installation_uuid : (string) Str::uuid();

            // Re-assina a licença com os dados atualizados
            $activationKey = $this->licenseIssuer->issue($license, $modulesKeys, $installationUuid);

            // Log de auditoria
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'action' => 'renew',
                'details' => [
                    'expires_at' => $expiresAt->toIso8601String(),
                    'modules' => $modulesKeys,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return [
                'success' => true,
                'license_uuid' => $license->uuid,
                'activation_key' => $activationKey,
                'status' => $license->status,
                'expires_at' => $license->expires_at ? $license->expires_at->toIso8601String() : null,
            ];
        });

        return response()->json($result, 200);
    }

    /**
     * POST /api/licenses/suspend
     * Suspende uma licença comercial.
     */
    public function suspend(Request $request): JsonResponse
    {
        $request->validate([
            'license_uuid' => 'required|uuid|exists:licenses,uuid',
        ]);

        $licenseUuid = $request->json('license_uuid');

        $result = DB::transaction(function () use ($licenseUuid) {
            /** @var License $license */
            $license = License::where('uuid', $licenseUuid)->firstOrFail();

            $license->update(['status' => 'suspended']);

            $modulesKeys = $license->modules()->pluck('code')->toArray();
            $activation = $license->activations()->where('status', 'active')->first();
            $installationUuid = $activation ? $activation->installation_uuid : (string) Str::uuid();

            // Re-assina informando o novo status suspenso
            $activationKey = $this->licenseIssuer->issue($license, $modulesKeys, $installationUuid);

            // Log de auditoria
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'action' => 'suspend',
                'details' => ['status' => 'suspended'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return [
                'success' => true,
                'status' => 'suspended',
                'activation_key' => $activationKey,
            ];
        });

        return response()->json($result, 200);
    }

    /**
     * POST /api/licenses/cancel
     * Cancela uma licença comercial.
     */
    public function cancel(Request $request): JsonResponse
    {
        $request->validate([
            'license_uuid' => 'required|uuid|exists:licenses,uuid',
        ]);

        $licenseUuid = $request->json('license_uuid');

        $result = DB::transaction(function () use ($licenseUuid) {
            /** @var License $license */
            $license = License::where('uuid', $licenseUuid)->firstOrFail();

            $license->update(['status' => 'cancelled']);

            // Desativa as ativações vinculadas
            $license->activations()->update([
                'status' => 'revoked',
                'revoked_at' => Carbon::now(),
            ]);

            $modulesKeys = $license->modules()->pluck('code')->toArray();
            $activation = $license->activations()->first();
            $installationUuid = $activation ? $activation->installation_uuid : (string) Str::uuid();

            // Re-assina informando o novo status cancelado
            $activationKey = $this->licenseIssuer->issue($license, $modulesKeys, $installationUuid);

            // Log de auditoria
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'action' => 'cancel',
                'details' => ['status' => 'cancelled'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return [
                'success' => true,
                'status' => 'cancelled',
                'activation_key' => $activationKey,
            ];
        });

        return response()->json($result, 200);
    }

    /**
     * GET /api/licenses/{uuid}
     * Retorna os detalhes de uma licença pelo UUID.
     */
    public function show(string $uuid): JsonResponse
    {
        /** @var License $license */
        $license = License::where('uuid', $uuid)->with(['modules', 'activations'])->firstOrFail();

        return response()->json($license);
    }

    /**
     * GET /api/installations/{uuid}
     * Retorna os detalhes de uma instalação pelo UUID.
     */
    public function showInstallation(string $uuid): JsonResponse
    {
        /** @var LicenseInstallation $installation */
        $installation = LicenseInstallation::where('uuid', $uuid)->firstOrFail();

        return response()->json($installation);
    }

    /**
     * GET /api/modules
     * Retorna todos os módulos do catálogo comercial.
     */
    public function modules(): JsonResponse
    {
        $modules = Module::where('status', 'active')->get();

        return response()->json($modules);
    }
}
