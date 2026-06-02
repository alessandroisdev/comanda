<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseAuditLog;
use App\Models\LicenseInstallation;
use App\Models\Module;
use App\Services\Licensing\LicenseIssuerService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalController extends Controller
{
    private LicenseIssuerService $licenseIssuer;

    public function __construct(LicenseIssuerService $licenseIssuer)
    {
        $this->licenseIssuer = $licenseIssuer;
    }

    public function dashboard(): View
    {
        $totalLicenses = License::count();
        $totalActivations = LicenseActivation::where('status', 'active')->count();
        $totalInstallations = LicenseInstallation::count();
        $totalModules = Module::count();

        $recentLicenses = License::orderBy('created_at', 'desc')->take(5)->get();
        $recentAuditLogs = LicenseAuditLog::with('license')->orderBy('created_at', 'desc')->take(5)->get();

        return view('portal.dashboard', compact(
            'totalLicenses',
            'totalActivations',
            'totalInstallations',
            'totalModules',
            'recentLicenses',
            'recentAuditLogs'
        ));
    }

    public function licenses(): View
    {
        $licenses = License::with('modules')->orderBy('created_at', 'desc')->get();
        $modules = Module::where('status', 'active')->get();

        return view('portal.licenses', compact('licenses', 'modules'));
    }

    public function storeLicense(Request $request): RedirectResponse
    {
        $request->validate([
            'client_name' => 'required|string|max:150',
            'client_email' => 'required|email|max:150',
            'client_document' => 'required|string|max:30',
            'plan_name' => 'required|string|max:100',
            'type' => 'required|string|in:trial,subscription,perpetual,developer,internal',
            'modules' => 'required|array',
            'expires_at' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request) {
            /** @var License $license */
            $license = License::create([
                'client_name' => $request->post('client_name'),
                'client_email' => $request->post('client_email'),
                'client_document' => $request->post('client_document'),
                'plan_name' => $request->post('plan_name'),
                'type' => $request->post('type'),
                'status' => $request->post('type') === 'trial' ? 'trial' : 'active',
                'issued_at' => Carbon::now(),
                'expires_at' => $request->post('expires_at') ? Carbon::parse($request->post('expires_at')) : Carbon::now()->addYear(),
            ]);

            $moduleIds = Module::whereIn('code', $request->post('modules'))->pluck('id');
            $license->modules()->sync($moduleIds);

            // Gera log de auditoria
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'action' => 'issue',
                'details' => [
                    'type' => $license->type,
                    'plan_name' => $license->plan_name,
                    'modules' => $request->post('modules'),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect('/portal/licenses')->with('success', 'Licença emitida com sucesso!');
    }

    public function updateLicense(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'client_name' => 'required|string|max:150',
            'client_email' => 'required|email|max:150',
            'client_document' => 'required|string|max:30',
            'plan_name' => 'required|string|max:100',
            'type' => 'required|string|in:trial,subscription,perpetual,developer,internal',
            'status' => 'required|string|in:active,trial,expired,suspended,cancelled,blocked',
            'modules' => 'required|array',
            'expires_at' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $id) {
            /** @var License $license */
            $license = License::findOrFail($id);

            $license->update([
                'client_name' => $request->post('client_name'),
                'client_email' => $request->post('client_email'),
                'client_document' => $request->post('client_document'),
                'plan_name' => $request->post('plan_name'),
                'type' => $request->post('type'),
                'status' => $request->post('status'),
                'expires_at' => $request->post('expires_at') ? Carbon::parse($request->post('expires_at')) : null,
            ]);

            $moduleIds = Module::whereIn('code', $request->post('modules'))->pluck('id');
            $license->modules()->sync($moduleIds);

            // Re-assina a licença
            $modulesKeys = $request->post('modules');
            $activation = $license->activations()->where('status', 'active')->first();
            $installationUuid = $activation ? $activation->installation_uuid : (string) Str::uuid();

            $this->licenseIssuer->issue($license, $modulesKeys, $installationUuid, null);

            // Log de auditoria
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'action' => 'edit',
                'details' => [
                    'client_name' => $license->client_name,
                    'type' => $license->type,
                    'status' => $license->status,
                    'modules' => $modulesKeys,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect('/portal/licenses')->with('success', 'Licença atualizada com sucesso!');
    }

    public function renewLicense(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'expires_at' => 'required|date',
        ]);

        DB::transaction(function () use ($request, $id) {
            /** @var License $license */
            $license = License::findOrFail($id);

            $license->update([
                'expires_at' => Carbon::parse($request->post('expires_at')),
                'status' => $license->type === 'trial' ? 'trial' : 'active',
            ]);

            $modulesKeys = $license->modules()->pluck('code')->toArray();
            $activation = $license->activations()->where('status', 'active')->first();
            $installationUuid = $activation ? $activation->installation_uuid : (string) Str::uuid();

            // Re-assina a licença
            $this->licenseIssuer->issue($license, $modulesKeys, $installationUuid, null);

            // Log de auditoria
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'action' => 'renew',
                'details' => ['expires_at' => $license->expires_at->toIso8601String()],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return redirect('/portal/licenses')->with('success', 'Licença renovada com sucesso!');
    }

    public function suspendLicense(string $id): RedirectResponse
    {
        DB::transaction(function () use ($id) {
            /** @var License $license */
            $license = License::findOrFail($id);

            $license->update(['status' => 'suspended']);

            $modulesKeys = $license->modules()->pluck('code')->toArray();
            $activation = $license->activations()->where('status', 'active')->first();
            $installationUuid = $activation ? $activation->installation_uuid : (string) Str::uuid();

            $this->licenseIssuer->issue($license, $modulesKeys, $installationUuid, null);

            // Log de auditoria
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'action' => 'suspend',
                'details' => ['status' => 'suspended'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return redirect('/portal/licenses')->with('success', 'Licença suspensa!');
    }

    public function cancelLicense(string $id): RedirectResponse
    {
        DB::transaction(function () use ($id) {
            /** @var License $license */
            $license = License::findOrFail($id);

            $license->update(['status' => 'cancelled']);

            $license->activations()->update([
                'status' => 'revoked',
                'revoked_at' => Carbon::now(),
            ]);

            $modulesKeys = $license->modules()->pluck('code')->toArray();
            $activation = $license->activations()->first();
            $installationUuid = $activation ? $activation->installation_uuid : (string) Str::uuid();

            $this->licenseIssuer->issue($license, $modulesKeys, $installationUuid, null);

            // Log de auditoria
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'action' => 'cancel',
                'details' => ['status' => 'cancelled'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return redirect('/portal/licenses')->with('success', 'Licença cancelada de forma permanente!');
    }

    public function modules(): View
    {
        $modules = Module::orderBy('code', 'asc')->get();

        return view('portal.modules', compact('modules'));
    }

    public function storeModule(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:modules,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'version_min' => 'required|string|max:30',
            'price_suggested_cents' => 'required|integer',
        ]);

        Module::create([
            'uuid' => (string) Str::uuid(),
            'code' => $request->post('code'),
            'name' => $request->post('name'),
            'description' => $request->post('description'),
            'status' => 'active',
            'dependencies' => [],
            'version_min' => $request->post('version_min'),
            'price_suggested_cents' => (int) $request->post('price_suggested_cents'),
        ]);

        return redirect('/portal/modules')->with('success', 'Módulo comercial cadastrado com sucesso!');
    }

    public function toggleModule(string $id): RedirectResponse
    {
        /** @var Module $module */
        $module = Module::findOrFail($id);
        $newStatus = $module->status === 'active' ? 'inactive' : 'active';
        $module->update(['status' => $newStatus]);

        return redirect('/portal/modules')->with('success', "Módulo alterado para {$newStatus}!");
    }

    public function installations(): View
    {
        $installations = LicenseInstallation::orderBy('created_at', 'desc')->get();

        return view('portal.installations', compact('installations'));
    }

    public function toggleInstallation(string $id): RedirectResponse
    {
        /** @var LicenseInstallation $installation */
        $installation = LicenseInstallation::findOrFail($id);
        $newStatus = $installation->status === 'active' ? 'blocked' : 'active';
        $installation->update(['status' => $newStatus]);

        return redirect('/portal/installations')->with('success', "Instalação física atualizada para {$newStatus}!");
    }

    public function audit(): View
    {
        $logs = LicenseAuditLog::with('license')->orderBy('created_at', 'desc')->get();

        return view('portal.audit', compact('logs'));
    }
}
