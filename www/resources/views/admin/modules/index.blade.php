@extends('layouts.admin')

@section('title', 'Módulos & Licenciamento')
@section('page_title', 'Módulos Ativos & Status de Licenciamento')

@section('content')
<div class="mb-4">
    <p class="text-muted m-0">Abaixo estão listados todos os recursos comerciais e de infraestrutura orquestrados. Módulos comerciais dependem de chaves RSA válidas e licenças ativas.</p>
</div>

<!-- Grid de Módulos -->
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    @foreach($modules as $module)
        <div class="col">
            <div class="card card-premium h-100 p-3 d-flex flex-column" style="position: relative; overflow: hidden;">
                <!-- Efeito sutil de background dependendo do status -->
                @if($module['is_active'])
                    <div style="position: absolute; top: 0; right: 0; width: 6px; height: 100%; background: linear-gradient(180deg, #10b981, #059669);"></div>
                @else
                    <div style="position: absolute; top: 0; right: 0; width: 6px; height: 100%; background: linear-gradient(180deg, #ef4444, #dc2626);"></div>
                @endif

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <!-- Escolher ícone apropriado de forma dinâmica -->
                        @php
                            $icon = match($module['key']) {
                                'admin' => 'bi-shield-lock-fill',
                                'api' => 'bi-braces-asterisk',
                                'licensing' => 'bi-key-fill',
                                'pdv' => 'bi-cash-coin',
                                'waiter' => 'bi-bell-fill',
                                'kitchen' => 'bi-egg-fried',
                                'hall' => 'bi-door-open-fill',
                                'delivery' => 'bi-truck',
                                'tablet_table' => 'bi-tablet-landscape-fill',
                                'kiosk' => 'bi-display-fill',
                                'digital_menu' => 'bi-qr-code-scan',
                                'printing' => 'bi-printer-fill',
                                default => 'bi-plugin'
                            };
                        @endphp
                        <div class="rounded-3 bg-slate-800 border border-slate-700 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.4rem;">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <div>
                            <h5 class="m-0 text-white fw-bold">{{ $module['name'] }}</h5>
                            <span class="text-muted" style="font-size: 0.75rem;">v{{ $module['version'] }}</span>
                        </div>
                    </div>

                    <!-- Ponto de Status Pulsante -->
                    <div>
                        @if($module['is_active'])
                            <span class="badge-premium-active d-inline-flex align-items-center gap-2">
                                <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px; animation-duration: 1.5s;"></span> Ativo
                            </span>
                        @else
                            <span class="badge-premium-inactive d-inline-flex align-items-center gap-2">
                                <span class="d-inline-block rounded-circle bg-danger" style="width: 8px; height: 8px;"></span> Inativo
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex-grow-1">
                    <p class="text-slate-300 font-sans" style="font-size: 0.88rem; line-height: 1.5;">
                        {{ $module['description'] }}
                    </p>
                </div>

                <!-- Detalhes Finais e Dependências -->
                <div class="mt-4 pt-3 border-top border-slate-800 d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span class="text-muted">Tipo de Recurso:</span>
                        @if($module['core'])
                            <span class="text-primary fw-bold">Estrutural (Core)</span>
                        @else
                            <span class="text-purple-400 fw-bold">Comercial (Módulo)</span>
                        @endif
                    </div>

                    @if(!empty($module['dependencies']))
                        <div class="d-flex flex-wrap align-items-center gap-1 mt-1" style="font-size: 0.75rem;">
                            <span class="text-muted me-1">Dependências:</span>
                            @foreach($module['dependencies'] as $dep)
                                <span class="badge bg-slate-800 border border-slate-700 text-slate-300">{{ $dep }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
