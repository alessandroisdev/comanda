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
            background-color: #f8fafc; /* Slate 50 - Fundo limpo e corporativo */
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
            color: #0f172a; /* Slate 900 */
        }
        
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.05em;
            color: #0f172a !important;
        }
        
        /* Sidebar Vercel-like minimalista e polida */
        .sidebar {
            background-color: #ffffff;
            min-height: 100vh;
            border-right: 1px solid #e2e8f0;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.02);
            color: #0f172a;
            z-index: 100;
        }
        
        .sidebar .nav-link {
            color: #475569; /* Slate 600 */
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
            transform: translateX(2px);
        }
        
        .sidebar .nav-link.active {
            background-color: #f1f5f9;
            color: #0284c7; /* Azul Royal Accent */
            font-weight: 600;
            border-left: 3px solid #0284c7;
        }
        
        .sidebar .nav-link i {
            font-size: 1.1rem;
            transition: transform 0.2s;
        }
        
        .sidebar .nav-link:hover i {
            transform: scale(1.1);
        }

        /* Cartões Corporativos com Bordas Nítidas */
        .card {
            border: 1px solid #cbd5e1 !important; /* Slate 300 - Contraste e divisórias nítidas */
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            background: #ffffff;
        }
        
        .card-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #cbd5e1 !important;
            font-weight: 600;
            color: #0f172a;
        }

        /* Tabelas com Alto Contraste */
        .table-responsive {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        
        .table {
            color: #334155 !important; /* Slate 700 para leitura confortável */
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f1f5f9 !important; /* Slate 100 */
            color: #0f172a !important; /* Slate 900 - Nítido */
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #cbd5e1 !important;
            padding: 12px 16px;
        }
        
        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8fafc;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }

        /* Formulários e Inputs Corporativos Nítidos */
        .form-label {
            color: #1e293b !important; /* Slate 800 */
            font-weight: 600;
            font-size: 0.88rem;
            margin-bottom: 0.35rem;
        }
        
        .form-control, .form-select {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important; /* Slate 300 - Garante leitura do contorno */
            color: #0f172a !important;
            border-radius: 6px !important;
            padding: 0.55rem 0.75rem !important;
            transition: all 0.2s ease-in-out;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0284c7 !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
            outline: none;
        }
        
        .form-control::placeholder {
            color: #94a3b8 !important; /* Slate 400 */
        }

        /* Botões Premium */
        .btn-primary {
            background-color: #0284c7 !important;
            border-color: #0284c7 !important;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(2, 132, 199, 0.05);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .btn-primary:hover {
            background-color: #0369a1 !important;
            border-color: #0369a1 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }

        /* Badges de Status Limpos e de Alto Contraste */
        .badge {
            font-weight: 600 !important;
            padding: 5px 10px !important;
            border-radius: 12px !important;
            font-size: 0.75rem !important;
        }
        
        .bg-success {
            background-color: #dcfce7 !important; /* Soft Green */
            color: #15803d !important; /* Dark Green */
            border: 1px solid #bbf7d0 !important;
        }
        
        .bg-danger {
            background-color: #fee2e2 !important; /* Soft Red */
            color: #b91c1c !important; /* Dark Red */
            border: 1px solid #fecaca !important;
        }
        
        .bg-warning {
            background-color: #fef3c7 !important; /* Soft Amber */
            color: #b45309 !important; /* Dark Amber */
            border: 1px solid #fde68a !important;
        }
        
        .bg-info {
            background-color: #e0f2fe !important; /* Soft Blue */
            color: #0369a1 !important; /* Dark Blue */
            border: 1px solid #bae6fd !important;
        }
        
        .bg-secondary {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0 !important;
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
