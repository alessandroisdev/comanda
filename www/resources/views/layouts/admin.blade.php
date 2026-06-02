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
    <link rel="manifest" href="/manifest.json">


    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #020617; /* Slate 950 */
            color: #f8fafc; /* Slate 50 */
            overflow-x: hidden;
        }

        /* Scrollbar Personalizada Premium */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #020617;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }

        /* Glassmorphism Sidebar */
        .admin-sidebar {
            width: 280px;
            background: rgba(15, 23, 42, 0.85); /* Slate 900 */
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(51, 65, 85, 0.5);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }

        .brand-logo {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        /* Sidebar Nav Item Styling */
        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            color: #94a3b8; /* Slate 400 */
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            margin-bottom: 4px;
        }
        .nav-link-custom:hover {
            color: #f8fafc;
            background: rgba(30, 41, 59, 0.6);
            transform: translateX(4px);
        }
        .nav-link-custom i {
            font-size: 1.2rem;
            margin-right: 12px;
            transition: transform 0.2s;
        }
        .nav-link-custom:hover i {
            transform: scale(1.1);
        }
        .nav-link-custom.active {
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35);
        }
        .nav-link-custom.active i {
            color: #ffffff;
        }

        /* Header de Ações */
        .admin-header {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(51, 65, 85, 0.4);
            height: 70px;
        }

        /* Cards Administrativos Premium */
        .card-premium {
            background: #0f172a; /* Slate 900 */
            border: 1px solid #1e293b;
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
            transition: transform 0.2s, border-color 0.2s;
        }
        .card-premium:hover {
            border-color: #334155;
        }

        .btn-premium-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
            transition: all 0.2s ease;
        }
        .btn-premium-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.45);
        }

        .badge-premium-active {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-premium-inactive {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
    @yield('styles')
</head>
<body class="h-100 d-flex flex-column">

    <div class="d-flex h-100 flex-row overflow-hidden">
        <!-- Sidebar Esquerda -->
        <aside class="admin-sidebar d-flex flex-column p-4 flex-shrink-0">
            <div class="d-flex align-items-center mb-4 pb-2 border-bottom border-slate-800">
                <span class="fs-3 brand-logo"><i class="bi bi-rocket-takeoff-fill"></i> COMANDA</span>
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

