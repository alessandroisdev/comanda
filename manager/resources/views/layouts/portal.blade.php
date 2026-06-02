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
            background-color: #f4f6f9;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .sidebar {
            background-color: #1e293b;
            min-height: 100vh;
            color: #f8fafc;
        }
        .sidebar .nav-link {
            color: #cbd5e1;
            font-weight: 500;
            padding: 0.8rem 1.5rem;
            border-radius: 0.375rem;
            margin: 0.2rem 1rem;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #334155;
            color: #ffffff;
        }
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .table-responsive {
            background: #fff;
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
                <a class="navbar-brand text-white fs-4 d-flex align-items-center" href="/portal">
                    <span class="fw-bold">Comanda</span><span class="text-primary fw-light ms-1">Manager</span>
                </a>
            </div>
            <hr class="mx-3 my-2 text-secondary">
            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/portal/dashboard">
                        📊 Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/portal/licenses">
                        🔑 Licenças & Contratos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/portal/installations">
                        💻 Instalações Físicas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/portal/modules">
                        🏷️ Catálogo de Módulos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/portal/audit">
                        📜 Auditoria Comercial
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('portal/help') ? 'active' : '' }}" href="/portal/help">
                        ❓ Ajuda e Integração
                    </a>
                </li>

            </ul>

            <div class="p-3">
                <div class="d-flex align-items-center text-white bg-dark p-3 rounded">
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
