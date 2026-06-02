@extends('layouts.portal')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Auditoria Comercial</h1>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Data / Hora</th>
                    <th>Ação</th>
                    <th>Licença / Cliente</th>
                    <th>UUID da Instalação</th>
                    <th>IP</th>
                    <th>Navegador / User Agent</th>
                    <th>Detalhes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}</td>
                        <td>
                            <span class="badge bg-dark">{{ strtoupper($log->action) }}</span>
                        </td>
                        <td>
                            <strong>{{ $log->license?->client_name ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $log->license?->client_email ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <code>{{ $log->installation_uuid ?? '-' }}</code>
                        </td>
                        <td><code>{{ $log->ip_address }}</code></td>
                        <td><small class="text-muted">{{ Str::limit($log->user_agent, 30) }}</small></td>
                        <td>
                            <pre class="bg-light p-2 rounded mb-0" style="font-size: 0.75rem;">{{ json_encode($log->details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">Nenhum registro de auditoria comercial.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
