<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Licensing\LicenseManager;
use App\Services\Monitoring\MetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private MetricsService $metricsService;

    private LicenseManager $licenseManager;

    public function __construct(MetricsService $metricsService, LicenseManager $licenseManager)
    {
        $this->metricsService = $metricsService;
        $this->licenseManager = $licenseManager;
    }

    /**
     * Renderiza o Dashboard Administrativo Executivo.
     */
    public function index(): View
    {
        // Coleta métricas consolidadas iniciais
        $metrics = $this->metricsService->getFullMetrics();
        $licenseAlert = $this->licenseManager->getLicenseAlert();
        $licenseData = $this->licenseManager->getLicenseData();

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'licenseAlert' => $licenseAlert,
            'licenseData' => $licenseData,
        ]);
    }

    /**
     * Endpoint API para obter as métricas em JSON sob demanda.
     */
    public function metrics(): JsonResponse
    {
        $metrics = $this->metricsService->getFullMetrics();

        return response()->json($metrics);
    }
}
