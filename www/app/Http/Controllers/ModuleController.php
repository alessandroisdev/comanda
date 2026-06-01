<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Licensing\ModuleAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function __construct(
        private readonly ModuleAccessService $moduleAccessService
    ) {}

    /**
     * Exibe a tela administrativa de módulos e licenciamento.
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

        return view('admin.modules.index', compact('modules'));
    }
}
