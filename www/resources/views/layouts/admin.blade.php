<!DOCTYPE html>
<html lang="pt_BR" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel Administrativo') — Comanda</title>

    <!-- Google Fonts & Bootstrap Icons carregados localmente via npm/Vite em app.css -->

    <!-- Vite Assets (Vite compilará localmente Bootstrap 5, DataTables.net e TypeScript) -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    <!-- Link do Manifesto PWA -->
    <link rel="manifest" href="/manifest.json">    <style>
        body {
            font-family: 'Outfit', system-ui, -apple-system, sans-serif;
            background-color: #09090b; /* Zinc 950 */
            color: #f4f4f5; /* Zinc 100 */
            overflow-x: hidden;
        }

        /* Scrollbar Personalizada Premium Fina */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #09090b;
        }
        ::-webkit-scrollbar-thumb {
            background: #27272a; /* Zinc 800 */
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #3f3f46; /* Zinc 700 */
        }

        /* Glassmorphism Sidebar */
        .admin-sidebar {
            width: 270px;
            background: rgba(18, 18, 20, 0.85); /* Zinc 900 equivalent translucent */
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(63, 63, 70, 0.3);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1000;
        }

        .brand-logo {
            font-weight: 800;
            letter-spacing: -0.05em;
            color: #ffffff;
            font-size: 1.35rem;
        }

        /* Sidebar Nav Item Styling */
        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            color: #a1a1aa; /* Zinc 400 */
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            margin-bottom: 4px;
            border-left: 3px solid transparent;
        }
        .nav-link-custom:hover {
            color: #f4f4f5; /* Zinc 100 */
            background: rgba(39, 39, 42, 0.4);
            transform: translateX(2px);
        }
        .nav-link-custom i {
            font-size: 1.1rem;
            margin-right: 10px;
            transition: transform 0.2s;
        }
        .nav-link-custom:hover i {
            transform: scale(1.05);
        }
        .nav-link-custom.active {
            color: #ffffff;
            background: rgba(39, 39, 42, 0.6);
            border-left: 3px solid #06b6d4; /* Accent Cyan */
            box-shadow: none;
        }
        .nav-link-custom.active i {
            color: #06b6d4;
        }

        /* Header de Ações */
        .admin-header {
            background: rgba(18, 18, 20, 0.6);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(63, 63, 70, 0.2);
            height: 70px;
        }

        /* Cards Administrativos Premium */
        .card-premium {
            background: #18181b; /* Zinc 900 */
            border: 1px solid rgba(63, 63, 70, 0.4);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            transition: border-color 0.2s;
        }
        .card-premium:hover {
            border-color: rgba(82, 82, 91, 0.6);
        }

        .btn-premium-primary {
            background: #0ea5e9; /* Sky 500 */
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-premium-primary:hover {
            background: #0284c7; /* Sky 600 */
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(14, 165, 233, 0.3);
        }
        .btn-premium-primary:active {
            transform: translateY(0) scale(0.98);
        }

        .badge-premium-active {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-premium-inactive {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
    @yield('styles')
</head>
<body class="h-100 d-flex flex-column">

    <div class="d-flex h-100 flex-row overflow-hidden">
        <!-- Sidebar Esquerda -->
        <aside class="admin-sidebar d-flex flex-column p-4 flex-shrink-0">
            <div class="d-flex align-items-center mb-4 pb-2 border-bottom border-zinc-800" style="border-bottom-color: rgba(63, 63, 70, 0.3) !important;">
                <span class="brand-logo">COMANDA<span style="color: #06b6d4;">.</span></span>
            </div>

            <!-- Navegação -->
            <nav class="nav flex-column flex-grow-1 overflow-y-auto">
                <a href="/" class="nav-link-custom {{ Request::is('/') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="/admin/companies" class="nav-link-custom {{ Request::is('admin/companies*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> Empresas
                </a>
                <a href="/admin/units" class="nav-link-custom {{ Request::is('admin/units*') ? 'active' : '' }}">
                    <i class="bi bi-geo-alt"></i> Unidades Físicas
                </a>
                <a href="/admin/users" class="nav-link-custom {{ Request::is('admin/users*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Usuários Painel
                </a>
                <a href="/admin/employees" class="nav-link-custom {{ Request::is('admin/employees*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge-fill"></i> Equipe / Equipes
                </a>
                <a href="/admin/customers" class="nav-link-custom {{ Request::is('admin/customers*') ? 'active' : '' }}">
                    <i class="bi bi-emoji-smile-fill"></i> Clientes
                </a>
                <a href="/admin/categories" class="nav-link-custom {{ Request::is('admin/categories*') ? 'active' : '' }}">
                    <i class="bi bi-tags-fill"></i> Categorias Menu
                </a>
                <a href="/admin/products" class="nav-link-custom {{ Request::is('admin/products*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam-fill"></i> Catálogo Produtos
                </a>

                <div class="text-uppercase text-muted fw-bold px-3 pt-3 pb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Operacional</div>
                
                <a href="/admin/tables" class="nav-link-custom {{ Request::is('admin/tables*') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap-fill"></i> Controle de Mesas
                </a>
                <a href="/admin/sessions" class="nav-link-custom {{ Request::is('admin/sessions*') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i> Comandas Operacionais
                </a>
                <a href="/admin/kitchen" class="nav-link-custom {{ Request::is('admin/kitchen*') ? 'active' : '' }}">
                    <i class="bi bi-fire"></i> Fila Cozinha
                </a>
                <a href="/admin/cashier" class="nav-link-custom {{ Request::is('admin/cashier*') ? 'active' : '' }}">
                    <i class="bi bi-cash-register"></i> Caixa Operacional
                </a>

                <div class="text-uppercase text-muted fw-bold px-3 pt-3 pb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">Configuração</div>

                <a href="/admin/modules" class="nav-link-custom {{ Request::is('admin/modules*') ? 'active' : '' }}">
                    <i class="bi bi-plugin"></i> Módulos & Licença
                </a>
            </nav>

            <!-- Rodapé da Sidebar -->
            <div class="mt-auto pt-3 border-top border-slate-800 d-flex align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white font-semibold" style="width: 40px; height: 40px;">
                        A
                    </div>
                    <div>
                        <h6 class="m-0 text-white font-semibold" style="font-size: 0.9rem;">Administrador</h6>
                        <span class="text-muted" style="font-size: 0.75rem;">admin@comanda.com</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Área de Conteúdo Direita -->
        <div class="d-flex flex-column flex-grow-1 h-100 overflow-hidden">
            <!-- Header do Topo -->
            <header class="admin-header d-flex align-items-center justify-content-between px-4">
                <div class="d-flex align-items-center">
                    <h4 class="m-0 text-white fw-bold">@yield('page_title', 'Visão Geral')</h4>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-slate-800 border border-slate-700 text-slate-300 p-2 rounded-3">
                        <i class="bi bi-clock-history me-1 text-primary"></i> 2026-06-01
                    </span>
                </div>
            </header>

            <!-- Corpo de Conteúdo Principal -->
            <main class="flex-grow-1 overflow-y-auto p-4">
                @php
                    $licenseAlert = null;
                    try {
                        $licenseAlert = app(\App\Services\Licensing\LicenseManager::class)->getLicenseAlert();
                    } catch (\Exception $e) {
                        // Silencia em caso de migrações incompletas em testes
                    }
                @endphp

                @if($licenseAlert)
                    <div class="alert alert-{{ $licenseAlert['type'] }} bg-{{ $licenseAlert['type'] }}-subtle border border-{{ $licenseAlert['type'] }} text-{{ $licenseAlert['type'] }} alert-dismissible fade show rounded-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {!! $licenseAlert['message'] !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Alertas Globais de Sucesso ou Erro -->
                @if(session('success'))
                    <div class="alert alert-success bg-success-subtle border border-success text-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger bg-danger-subtle border border-danger text-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts Bootstrap carregados dinamicamente via Vite global window -->
    @yield('scripts')

    <!-- Registro do Service Worker PWA e Acessibilidade de Modais -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => console.log('[PWA] Service Worker registrado com sucesso:', reg.scope))
                    .catch((err) => console.error('[PWA] Falha ao registrar Service Worker:', err));
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.modal').forEach((modal) => {
                modal.addEventListener('hide.bs.modal', () => {
                    if (document.activeElement instanceof HTMLElement) {
                        document.activeElement.blur();
                    }
                });
            });
        });
    </script>
</body>
</html>

