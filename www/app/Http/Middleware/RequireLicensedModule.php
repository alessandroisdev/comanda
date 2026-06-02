<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Licensing\ModuleAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireLicensedModule
{
    private ModuleAccessService $moduleAccess;

    public function __construct(ModuleAccessService $moduleAccess)
    {
        $this->moduleAccess = $moduleAccess;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        if (! $this->moduleAccess->hasAccess($moduleKey)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "Acesso bloqueado: o módulo comercial '{$moduleKey}' não está licenciado para esta instalação física.",
                ], 403);
            }

            abort(403, "Acesso bloqueado: o módulo comercial '{$moduleKey}' não está licenciado para esta instalação física.");
        }

        return $next($request);
    }
}
