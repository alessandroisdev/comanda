<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use App\Services\Qrcode\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CardapioController extends Controller
{
    public function __construct(private readonly QrCodeService $qrCodeService) {}

    /**
     * Exibe o cardápio digital público, com suporte a deep link de mesa via slug.
     */
    public function index(Request $request, ?string $slug = null)
    {
        $table = null;
        if ($slug) {
            $table = Table::where('slug', $slug)->first();
            if ($table instanceof Table) {
                // Armazena a mesa e tenant na sessão do cliente
                session([
                    'public_table_uuid' => $table->public_uuid,
                    'company_id' => $table->company_id,
                    'unit_id' => $table->unit_id,
                ]);
            }
        }

        // Recupera dados com cache Redis e ETag
        $companyId = ($table instanceof Table) ? $table->company_id : $request->get('company_id', 1);
        $cacheKey = "menu_public:{$companyId}";

        $categories = Cache::remember($cacheKey, 600, function () use ($companyId) {
            return Category::where('company_id', $companyId)
                ->where('status', 'active')
                ->with(['products' => function ($q) {
                    $q->where('status', 'active')->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();
        });

        // Metadados dinâmicos de SEO e Schema.org
        $seo = [
            'title' => ($table instanceof Table) ? "Mesa {$table->name} — Cardápio Digital" : 'Cardápio Digital Oficial',
            'description' => 'Explore nosso catálogo digital completo, faça seus pedidos e aproveite nossos combos e promoções exclusivas.',
            'canonical' => ($table instanceof Table) ? route('public.menu.table', ['slug' => $table->slug]) : route('public.menu'),
            'image' => asset('/js/icon-512.png'),
        ];


        return view('public.menu.index', compact('categories', 'table', 'seo'));
    }

    /**
     * Endpoint para download ou exibição do QR Code SVG da mesa.
     */
    public function qrcode(string $publicUuid)
    {
        /** @var Table $table */
        $table = Table::where('public_uuid', $publicUuid)->firstOrFail();
        $url = route('public.menu.table', ['slug' => $table->slug]);

        $svg = $this->qrCodeService->generate($url);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qrcode-mesa-' . $table->code . '.svg"',
            'Cache-Control' => 'max-age=86400, public',
        ]);
    }

}
