@extends('layouts.admin')

@section('title', 'Dashboard Executivo')
@section('page_title', 'Dashboard Executivo')

@section('styles')
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .widget-card {
        background: rgba(30, 41, 59, 0.45);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .widget-card:hover {
        transform: translateY(-5px);
        border-color: rgba(59, 130, 246, 0.4);
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.1);
    }

    .widget-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
        pointer-events: none;
    }

    .widget-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        flex-shrink: 0;
        transition: transform 0.3s;
    }

    .widget-card:hover .widget-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .icon-sales { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .icon-production { background: rgba(245, 158, 11L, 0.15); color: #f59e0b; }
    .icon-delivery { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .icon-tables { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
    .icon-errors { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .icon-queue { background: rgba(100, 116, 139, 0.15); color: #94a3b8; }

    .widget-details {
        flex-grow: 1;
    }

    .widget-title {
        font-size: 0.85rem;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    .widget-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.02em;
    }

    /* Telemetry Panel */
    .telemetry-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .telemetry-card {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        padding: 24px;
    }

    .telemetry-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding-bottom: 15px;
    }

    .telemetry-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .progress-premium {
        height: 8px;
        background-color: rgba(255, 255, 255, 0.08);
        border-radius: 4px;
        overflow: hidden;
    }

    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 10px #10b981;
        animation: pulse-ring 1.5s infinite;
    }

    @keyframes pulse-ring {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>
@endsection

@section('content')
<!-- Grid de Widgets Operacionais -->
<div class="dashboard-grid">
    <!-- Vendas Hoje -->
    <div class="widget-card">
        <div class="widget-icon icon-sales">
            <i class="bi bi-currency-dollar"></i>
        </div>
        <div class="widget-details">
            <div class="widget-title">Vendas Hoje</div>
            <div class="widget-value" id="val-sales">R$ {{ number_format($metrics['business']['sales_today_cents'] / 100, 2, ',', '.') }}</div>
            <div class="text-muted" style="font-size: 0.75rem;">Ticket Médio: <span id="val-avg-ticket">R$ {{ number_format($metrics['business']['average_ticket_cents'] / 100, 2, ',', '.') }}</span></div>
        </div>
    </div>

    <!-- Cozinha / Produção -->
    <div class="widget-card">
        <div class="widget-icon icon-production">
            <i class="bi bi-fire"></i>
        </div>
        <div class="widget-details">
            <div class="widget-title">Em Produção</div>
            <div class="widget-value" id="val-production">{{ $metrics['business']['orders_in_production'] }} pratos</div>
            <div class="text-muted" style="font-size: 0.75rem;">Última hora: <span id="val-recent-orders">{{ $metrics['business']['orders_last_hour'] }} pedidos</span></div>
        </div>
    </div>

    <!-- Delivery -->
    <div class="widget-card">
        <div class="widget-icon icon-delivery">
            <i class="bi bi-truck"></i>
        </div>
        <div class="widget-details">
            <div class="widget-title">Delivery Ativo</div>
            <div class="widget-value" id="val-delivery">{{ $metrics['business']['deliveries_in_progress'] }} envios</div>
            <div class="text-muted" style="font-size: 0.75rem;">Modo de Canais Digitais ativado</div>
        </div>
    </div>

    <!-- Mesas Ocupadas -->
    <div class="widget-card">
        <div class="widget-icon icon-tables">
            <i class="bi bi-grid-3x3-gap"></i>
        </div>
        <div class="widget-details">
            <div class="widget-title">Mesas Ocupadas</div>
            <div class="widget-value" id="val-tables">{{ $metrics['business']['occupied_tables'] }} mesas</div>
            <div class="text-muted" style="font-size: 0.75rem;">Painel do Salão integrado</div>
        </div>
    </div>
</div>

<div class="telemetry-row">
    <!-- Telemetria de Sistema -->
    <div class="telemetry-card">
        <div class="telemetry-header">
            <div class="telemetry-title">
                <i class="bi bi-cpu-fill text-primary"></i> Saúde do Servidor & Infraestrutura
            </div>
            <div>
                <span class="badge bg-slate-800 border border-slate-700 text-slate-300">
                    <span class="pulse-dot me-1"></span> SSE Realtime Ativo
                </span>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- CPU -->
            <div class="col-md-4">
                <div class="p-3 rounded-3 bg-slate-800 border border-slate-700">
                    <div class="text-muted mb-2" style="font-size: 0.8rem;">Uso de CPU</div>
                    <h4 class="text-white fw-bold m-0" id="val-cpu">{{ $metrics['system']['cpu_load_percent'] }}%</h4>
                    <div class="progress-premium mt-2">
                        <div class="progress-bar bg-primary" id="bar-cpu" style="width: {{ $metrics['system']['cpu_load_percent'] }}%"></div>
                    </div>
                </div>
            </div>
            <!-- RAM -->
            <div class="col-md-4">
                <div class="p-3 rounded-3 bg-slate-800 border border-slate-700">
                    <div class="text-muted mb-2" style="font-size: 0.8rem;">Uso de Memória</div>
                    <h4 class="text-white fw-bold m-0" id="val-ram">{{ $metrics['system']['memory']['used_percent'] }}%</h4>
                    <div class="text-muted" style="font-size: 0.7rem; margin-top: 2px;" id="val-ram-raw">
                        {{ number_format($metrics['system']['memory']['used_bytes'] / (1024 * 1024 * 1024), 2) }} GB / {{ number_format($metrics['system']['memory']['total_bytes'] / (1024 * 1024 * 1024), 2) }} GB
                    </div>
                    <div class="progress-premium mt-2">
                        <div class="progress-bar bg-info" id="bar-ram" style="width: {{ $metrics['system']['memory']['used_percent'] }}%"></div>
                    </div>
                </div>
            </div>
            <!-- DISK -->
            <div class="col-md-4">
                <div class="p-3 rounded-3 bg-slate-800 border border-slate-700">
                    <div class="text-muted mb-2" style="font-size: 0.8rem;">Uso de Disco</div>
                    <h4 class="text-white fw-bold m-0" id="val-disk">{{ $metrics['system']['disk']['used_percent'] }}%</h4>
                    <div class="text-muted" style="font-size: 0.7rem; margin-top: 2px;" id="val-disk-raw">
                        {{ number_format($metrics['system']['disk']['used_bytes'] / (1024 * 1024 * 1024), 1) }} GB / {{ number_format($metrics['system']['disk']['total_bytes'] / (1024 * 1024 * 1024), 1) }} GB
                    </div>
                    <div class="progress-premium mt-2">
                        <div class="progress-bar bg-warning" id="bar-disk" style="width: {{ $metrics['system']['disk']['used_percent'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Banco de Dados -->
            <div class="col-md-6">
                <div class="p-3 rounded-3 bg-slate-800 border border-slate-700">
                    <div class="text-muted mb-2" style="font-size: 0.8rem;">Métricas de Banco (MySQL)</div>
                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                        <span>Conexões Ativas:</span>
                        <strong class="text-white" id="val-db-connections">{{ $metrics['database']['connections'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size: 0.85rem;">
                        <span>Consultas Lentas (Slow):</span>
                        <strong class="text-white" id="val-db-slow-queries">{{ $metrics['database']['slow_queries_count'] }}</strong>
                    </div>
                </div>
            </div>
            <!-- Filas Redis -->
            <div class="col-md-6">
                <div class="p-3 rounded-3 bg-slate-800 border border-slate-700">
                    <div class="text-muted mb-2" style="font-size: 0.8rem;">Fila de Processamento (Redis)</div>
                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                        <span>Jobs na Fila:</span>
                        <strong class="text-white" id="val-queue-pending">{{ $metrics['queue']['pending_jobs'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size: 0.85rem;">
                        <span>Jobs com Falhas:</span>
                        <strong class="text-white" id="val-queue-failed">{{ $metrics['queue']['failed_jobs'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Licenciamento & Contrato -->
    <div class="telemetry-card d-flex flex-column justify-content-between">
        <div>
            <div class="telemetry-header">
                <div class="telemetry-title">
                    <i class="bi bi-shield-lock-fill text-warning"></i> Licença Corporativa
                </div>
            </div>

            @if($licenseData)
                <div class="mb-3">
                    <div class="text-muted" style="font-size: 0.8rem;">Cliente Licenciado</div>
                    <div class="text-white fw-bold" style="font-size: 1.1rem;">{{ $licenseData['client_name'] ?? 'Instalação Local' }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">{{ $licenseData['client_document'] ?? '' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted" style="font-size: 0.8rem;">Plano Comercial</div>
                    <span class="badge bg-primary text-white border border-primary px-3 py-1 rounded-pill mt-1">
                        {{ strtoupper($licenseData['plan_name'] ?? 'Enterprise') }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="text-muted" style="font-size: 0.8rem;">Expiração do Contrato</div>
                    <div class="text-white" style="font-size: 0.95rem;">
                        {{ \Carbon\Carbon::parse($licenseData['expires_at'])->format('d/m/Y H:i') }}
                    </div>
                </div>

                <div class="mb-1">
                    <div class="text-muted" style="font-size: 0.8rem;">Módulos Licenciados</div>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        @foreach($licenseData['modules'] ?? [] as $mod)
                            <span class="badge bg-slate-800 border border-slate-700 text-slate-300 rounded" style="font-size: 0.7rem;">
                                {{ $mod }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-shield-slash-fill text-danger fs-1"></i>
                    <p class="text-slate-400 mt-2" style="font-size: 0.85rem;">Nenhuma licença comercial instalada localmente.</p>
                </div>
            @endif
        </div>

        @if($licenseData)
            <div class="mt-4 pt-3 border-t border-slate-800 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                <span class="text-muted">ID Instalação:</span>
                <span class="text-white font-mono" style="font-size: 0.7rem;">{{ substr($licenseData['installation_uuid'] ?? '', 0, 8) }}...</span>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Conexão reativa com o canal SSE admin.dashboard
        const channel = 'admin.dashboard';
        const sseUrl = `/sse/stream/${channel}`;
        
        console.log(`[Dashboard] Conectando ao canal SSE: ${channel}`);
        const eventSource = new EventSource(sseUrl);

        eventSource.addEventListener('metrics.updated', (e) => {
            try {
                const metrics = JSON.parse(e.data);
                console.log('[Dashboard] Métricas atualizadas via SSE:', metrics);
                
                // 1. Atualizar cards de negócio
                const salesFormatted = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })
                    .format(metrics.business.sales_today_cents / 100);
                const avgTicketFormatted = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })
                    .format(metrics.business.average_ticket_cents / 100);

                document.getElementById('val-sales').innerText = salesFormatted;
                document.getElementById('val-avg-ticket').innerText = avgTicketFormatted;
                document.getElementById('val-production').innerText = `${metrics.business.orders_in_production} pratos`;
                document.getElementById('val-recent-orders').innerText = `${metrics.business.orders_last_hour} pedidos`;
                document.getElementById('val-delivery').innerText = `${metrics.business.deliveries_in_progress} envios`;
                document.getElementById('val-tables').innerText = `${metrics.business.occupied_tables} mesas`;

                // 2. Atualizar telemetria de sistema (host)
                document.getElementById('val-cpu').innerText = `${metrics.system.cpu_load_percent}%`;
                document.getElementById('bar-cpu').style.width = `${metrics.system.cpu_load_percent}%`;

                const ramUsedGb = metrics.system.memory.used_bytes / (1024 * 1024 * 1024);
                const ramTotalGb = metrics.system.memory.total_bytes / (1024 * 1024 * 1024);
                document.getElementById('val-ram').innerText = `${metrics.system.memory.used_percent}%`;
                document.getElementById('bar-ram').style.width = `${metrics.system.memory.used_percent}%`;
                document.getElementById('val-ram-raw').innerText = 
                    `${ramUsedGb.toFixed(2)} GB / ${ramTotalGb.toFixed(2)} GB`;

                const diskUsedGb = metrics.system.disk.used_bytes / (1024 * 1024 * 1024);
                const diskTotalGb = metrics.system.disk.total_bytes / (1024 * 1024 * 1024);
                document.getElementById('val-disk').innerText = `${metrics.system.disk.used_percent}%`;
                document.getElementById('bar-disk').style.width = `${metrics.system.disk.used_percent}%`;
                document.getElementById('val-disk-raw').innerText = 
                    `${diskUsedGb.toFixed(1)} GB / ${diskTotalGb.toFixed(1)} GB`;

                // 3. Métricas de banco e fila
                document.getElementById('val-db-connections').innerText = metrics.database.connections;
                document.getElementById('val-db-slow-queries').innerText = metrics.database.slow_queries_count;
                document.getElementById('val-queue-pending').innerText = metrics.queue.pending_jobs;
                document.getElementById('val-queue-failed').innerText = metrics.queue.failed_jobs;

            } catch (err) {
                console.error('[Dashboard] Erro ao parsear dados SSE:', err);
            }
        });

        eventSource.onerror = (err) => {
            console.warn('[Dashboard] Conexão SSE falhou. Tentando reconectar...', err);
        };
    });
</script>
@endsection
