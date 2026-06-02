@extends('layouts.portal')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Instalações Físicas</h1>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>UUID da Instalação</th>
                    <th>Servidor / Hostname</th>
                    <th>Domínio Associado</th>
                    <th>IP Público</th>
                    <th>Fingerprint</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($installations as $inst)
                    <tr>
                        <td><code>{{ $inst->uuid }}</code></td>
                        <td><strong>{{ $inst->hostname }}</strong></td>
                        <td>{{ $inst->domain ?? '-' }}</td>
                        <td><code>{{ $inst->ip_address }}</code></td>
                        <td><small class="text-muted">{{ Str::limit($inst->fingerprint, 20) }}</small></td>
                        <td>
                            @if($inst->status === 'active')
                                <span class="badge bg-success">Operativa</span>
                            @else
                                <span class="badge bg-danger">Bloqueada</span>
                            @endif
                        </td>
                        <td>
                            <form action="/portal/installations/{{ $inst->id }}/toggle" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    🚫 Bloquear / Desbloquear
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">Nenhuma instalação física registrada até o momento.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
