<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comanda — Portal Comercial Manager</title>
    <!-- Recursos compilados localmente via Vite (Bootstrap + Bootstrap Icons + Fontes) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f9fafb; /* Off-white premium */
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
            color: #0f172a;
        }
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.05em;
            color: #0f172a !important;
        }
        .sidebar {
            background-color: #ffffff;
            min-height: 100vh;
            border-right: 1px solid #e2e8f0;
            color: #0f172a;
        }
        .sidebar .nav-link {
            color: #475569;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            border-radius: 0.375rem;
            margin: 0.2rem 1rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .sidebar .nav-link.active {
            background-color: #f1f5f9;
            color: #0f172a;
            border-left: 3px solid #0284c7; /* Royal Blue Accent */
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            background: #ffffff;
        }
        .table-responsive {
            background: #ffffff;
            border-radius: 0.75rem;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0 sidebar d-flex flex-column">
            <div class="px-4 py-4">
                <a class="navbar-brand text-dark fs-4 d-flex align-items-center" href="/portal">
                    <span class="fw-bold">COMANDA</span><span class="text-primary fw-light ms-1" style="color: #0284c7 !important;">Manager</span>
                </a>
            </div>
            <hr class="mx-3 my-2" style="color: #e2e8f0;">
            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('portal/dashboard') ? 'active' : '' }}" href="/portal/dashboard">
                        <i class="bi bi-speedometer2 me-2"></i> Painel Geral
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('portal/licenses*') ? 'active' : '' }}" href="/portal/licenses">
                        <i class="bi bi-key-fill me-2"></i> Licenças & Contratos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('portal/installations*') ? 'active' : '' }}" href="/portal/installations">
                        <i class="bi bi-cpu-fill me-2"></i> Instalações Físicas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('portal/modules*') ? 'active' : '' }}" href="/portal/modules">
                        <i class="bi bi-plugin me-2"></i> Catálogo de Módulos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('portal/audit*') ? 'active' : '' }}" href="/portal/audit">
                        <i class="bi bi-shield-check me-2"></i> Auditoria Comercial
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('portal/help*') ? 'active' : '' }}" href="/portal/help">
                        <i class="bi bi-question-circle-fill me-2"></i> Ajuda e Integração
                    </a>
                </li>
            </ul>
            <div class="p-3">
                <div class="d-flex align-items-center text-dark bg-light p-3 rounded border border-slate-200">
                    <div>
                        <div class="fw-bold">Admin Comercial</div>
                        <small class="text-muted">admin@manager.com</small>
                    </div>
                </div>
            </div>
        </div>


        <!-- Main Content Area -->
        <div class="col-md-9 col-lg-10 px-md-4 py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<!-- Scripts de bootstrap carregados localmente via Vite -->
<script>
    // Remove o foco do elemento ativo dentro do modal antes de fechá-lo
    // para evitar erros de acessibilidade/aria-hidden nos navegadores.
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
