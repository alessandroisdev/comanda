<!DOCTYPE html>
<html lang="pt-BR" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Metadados de SEO robustos e dinâmicos -->
    <title>{{ $seo['title'] }} — Comanda</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    
    <!-- Open Graph (Facebook / WhatsApp / Discord) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seo['title'] }} — Comanda">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    <meta property="og:image" content="{{ $seo['image'] }}">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }} — Comanda">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    <meta name="twitter:image" content="{{ $seo['image'] }}">

    <!-- Recursos compilados localmente via Vite (Bootstrap + Bootstrap Icons + Fontes) -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    
    <!-- Manifesto PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">

    <!-- Schema.org Marcação Estruturada (Restaurante/Prato) -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "Restaurant",
      "name": "Comanda Premium",
      "image": "{{ $seo['image'] }}",
      "description": "{{ $seo['description'] }}",
      "servesCuisine": "Brasileira, Variada",
      "priceRange": "$$"
    }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #020617; /* Slate 950 */
            color: #f8fafc; /* Slate 50 */
            overflow-x: hidden;
        }

        .menu-header {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.95) 0%, rgba(2, 6, 23, 0.95) 100%);
            border-bottom: 1px solid #1e293b;
            backdrop-filter: blur(12px);
        }

        .btn-action-premium {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border: none;
            font-weight: 600;
            color: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            transition: all 0.2s;
        }
        .btn-action-premium:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.45);
        }

        .card-product {
            background: #0f172a; /* Slate 900 */
            border: 1px solid #1e293b;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .card-product:hover {
            transform: translateY(-2px);
            border-color: #334155;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .category-tab {
            color: #94a3b8;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 30px;
            border: 1px solid #1e293b;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .category-tab:hover, .category-tab.active {
            color: white;
            background: rgba(59, 130, 246, 0.15);
            border-color: #3b82f6;
        }
        
        .badge-sector {
            background: rgba(139, 92, 246, 0.15);
            color: #a78bfa;
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 30px;
            padding: 5px 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .offline-toast {
            display: none;
            background: #ef4444;
            color: white;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            border-radius: 30px;
            padding: 10px 24px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }
    </style>
</head>
<body class="h-100 d-flex flex-column">

    <!-- Header do PWA / Cardápio -->
    <header class="menu-header sticky-top py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-4 fw-extrabold text-white" style="letter-spacing: -0.03em;">
                <i class="bi bi-rocket-takeoff text-primary"></i> COMANDA
            </span>
            @if($table)
                <span class="badge-sector ms-2">
                    <i class="bi bi-geo-fill me-1"></i> Mesa {{ $table->name }}
                </span>
            @endif
        </div>
        
        <div class="d-flex align-items-center gap-2">
            @if($table)
                <!-- Botões Operacionais Rápidos via SSE -->
                <button class="btn btn-outline-warning btn-sm px-3 rounded-pill" id="btn-call-waiter">
                    <i class="bi bi-bell-fill me-1"></i> Chamar Garçom
                </button>
                <button class="btn btn-outline-light btn-sm px-3 rounded-pill" id="btn-request-bill">
                    <i class="bi bi-receipt me-1"></i> Conta
                </button>
            @endif
        </div>
    </header>

    <!-- Navegação de Categorias Lateral/Horizontal deslizável -->
    <div class="bg-slate-950 border-bottom border-slate-900 py-3 px-4 overflow-x-auto d-flex gap-2" style="scrollbar-width: none;">
        @foreach($categories as $category)
            <a href="#cat-{{ $category->uuid }}" class="category-tab">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    <!-- Container Principal do Cardápio -->
    <main class="flex-grow-1 p-4 container" style="max-width: 900px;">
        @if($table)
            <div class="alert alert-info bg-primary-subtle border border-primary text-primary alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> Você está conectado à **Mesa {{ $table->name }}**. Todos os pedidos feitos por esta tela serão enfileirados e servidos na sua mesa automaticamente.
            </div>
        @endif

        <div class="row g-4">
            @foreach($categories as $category)
                @if($category->products->isNotEmpty())
                    <div class="col-12" id="cat-{{ $category->uuid }}">
                        <h4 class="text-white fw-bold border-bottom border-slate-800 pb-2 mb-3 mt-4">
                            <i class="bi bi-chevron-right text-primary me-1"></i> {{ $category->name }}
                        </h4>
                    </div>

                    @foreach($category->products as $product)
                        <div class="col-md-6">
                            <div class="card-product d-flex p-3 h-100">
                                <div class="flex-grow-1">
                                    <h5 class="text-white fw-bold m-0">{{ $product->name }}</h5>
                                    <p class="text-muted small my-2" style="line-height: 1.4;">{{ $product->description }}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-slate-900">
                                        <span class="fs-5 fw-bold text-success">R$ {{ number_format($product->price_cents / 100, 2, ',', '.') }}</span>
                                        <button class="btn btn-action-premium btn-sm px-3 rounded-pill btn-add-cart" data-uuid="{{ $product->uuid }}">
                                            <i class="bi bi-plus-lg me-1"></i> Adicionar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    </main>

    <!-- Toast Offline Resiliente -->
    <div class="offline-toast" id="offline-toast">
        <i class="bi bi-wifi-off me-2 animate-pulse"></i> Modo Offline Ativo
    </div>

    <!-- Scripts de Reatividade, Offline e SSE (compilados localmente via Vite) -->
    <script type="module">
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Detecção Realtime de Status Offline/Online
            const toast = document.getElementById('offline-toast');
            
            function updateOnlineStatus() {
                if (navigator.onLine) {
                    toast.style.display = 'none';
                } else {
                    toast.style.display = 'block';
                }
            }

            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);
            updateOnlineStatus();

            // 2. Registro de Ações de Chamar Garçom e Conta via SSE
            @if($table)
                const tableUuid = "{{ $table->public_uuid }}";

                document.getElementById('btn-call-waiter')?.addEventListener('click', () => {
                    fetch(`/api/v1/tables/${tableUuid}/call-waiter`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            alert('Garçom chamado com sucesso! Aguarde na mesa.');
                        }
                    });
                });

                document.getElementById('btn-request-bill')?.addEventListener('click', () => {
                    if (confirm('Deseja fechar sua conta e solicitar a impressão?')) {
                        fetch(`/api/v1/tables/${tableUuid}/request-bill`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                alert('Conta solicitada com sucesso! O atendente trará seu cupom.');
                            }
                        });
                    }
                });
            @endif
        });
    </script>
</body>
</html>
