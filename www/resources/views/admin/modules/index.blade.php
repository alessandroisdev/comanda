@extends('layouts.admin')

@section('title', 'Módulos & Licenciamento')
@section('page_title', 'Módulos Ativos & Status de Licenciamento')

@section('content')
<div class="mb-4">
    <p class="text-muted m-0">Abaixo estão listados todos os recursos comerciais e de infraestrutura orquestrados. Módulos comerciais dependem de chaves RSA válidas e licenças ativas.</p>
</div>

<!-- Painel de Licenciamento Criptográfico e Ativação -->
<div class="card card-premium mb-5 p-4">
    <div class="row g-4">
        <!-- Coluna 1: Status Atual da Licença -->
        <div class="col-12 col-lg-6 d-flex flex-column justify-content-between border-end border-slate-800 pe-lg-4" style="border-right-color: rgba(63, 63, 70, 0.3) !important;">
            <div>
                <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-cyan"></i> Status do Licenciamento
                </h5>

                <div class="p-3 rounded-3 mb-4 bg-slate-900 border border-slate-800" style="background-color: rgba(18, 18, 20, 0.5);">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted" style="font-size: 0.85rem;">Status Operacional:</span>
                        @if($licenseStatus->isActive())
                            <span class="badge-premium-active d-inline-flex align-items-center gap-2">
                                <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></span> Ativo
                            </span>
                        @else
                            <span class="badge-premium-inactive d-inline-flex align-items-center gap-2">
                                <span class="d-inline-block rounded-circle bg-danger" style="width: 8px; height: 8px;"></span> Inativo / Pendente
                            </span>
                        @endif
                    </div>

                    @if($licenseData)
                        <div class="d-flex flex-column gap-2" style="font-size: 0.85rem;">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Cliente:</span>
                                <span class="text-slate-200 fw-bold">{{ $licenseData['client_name'] ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Plano contratado:</span>
                                <span class="text-slate-200">{{ $licenseData['plan_name'] ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Expiração:</span>
                                <span class="text-slate-200">{{ isset($licenseData['expires_at']) ? \Carbon\Carbon::parse($licenseData['expires_at'])->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                            @if($daysUntilExpiration !== null)
                                <div class="d-flex justify-content-between border-top border-slate-800 pt-2 mt-1">
                                    <span class="text-muted">Tempo Restante:</span>
                                    @if($daysUntilExpiration < 0)
                                        <span class="text-danger fw-bold">Expirada há {{ abs($daysUntilExpiration) }} dias</span>
                                    @else
                                        <span class="text-success fw-bold">{{ $daysUntilExpiration }} dias restantes</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-slate-400 m-0" style="font-size: 0.85rem;">Nenhuma chave de licença instalada localmente no momento. Por favor, ative a licença ao lado para liberar os módulos comerciais.</p>
                    @endif
                </div>
            </div>

            <!-- UUID da Instalação Física -->
            <div class="pt-3 border-top border-slate-800" style="border-top-color: rgba(63, 63, 70, 0.2) !important;">
                <label class="text-muted mb-2 d-block" style="font-size: 0.8rem; letter-spacing: 0.05em; text-uppercase: true;">Identificador da Instalação Física (UUID)</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-slate-900 border-slate-700 text-slate-300 font-monospace" style="font-size: 0.85rem; border-color: rgba(63, 63, 70, 0.4) !important;" id="installationUuid" value="{{ $installationUuid }}" readonly>
                    <button class="btn btn-outline-secondary border-slate-700 hover-slate-800" type="button" id="copyUuidBtn" title="Copiar UUID">
                        <i class="bi bi-clipboard" id="copyIcon"></i>
                    </button>
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Você precisará deste UUID para emitir ou vincular a licença no Portal do Manager Comercial.</small>
            </div>
        </div>

        <!-- Coluna 2: Ativação Online / Offline (Abas) -->
        <div class="col-12 col-lg-6 ps-lg-4">
            <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-key-fill text-cyan"></i> Ativação e Desbloqueio
            </h5>

            <!-- Tabs Navigation -->
            <ul class="nav nav-pills mb-3 border-bottom border-slate-800 pb-2" id="activationTab" role="tablist" style="border-bottom-color: rgba(63, 63, 70, 0.2) !important;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active bg-transparent border-0 text-slate-300 fw-bold px-3 py-1 me-2 rounded" id="online-tab" data-bs-toggle="tab" data-bs-target="#online-panel" type="button" role="tab" aria-controls="online-panel" aria-selected="true" style="font-size: 0.9rem;">
                        <i class="bi bi-globe me-1"></i> Online (Recomendado)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link bg-transparent border-0 text-slate-300 fw-bold px-3 py-1 rounded" id="offline-tab" data-bs-toggle="tab" data-bs-target="#offline-panel" type="button" role="tab" aria-controls="offline-panel" aria-selected="false" style="font-size: 0.9rem;">
                        <i class="bi bi-file-earmark-lock-fill me-1"></i> Offline / Manual
                    </button>
                </li>
            </ul>

            <!-- Alerta local de feedback dinâmico -->
            <div id="activationAlert" class="alert d-none rounded-3 p-3 mb-3 border" role="alert" style="font-size: 0.85rem;"></div>

            <!-- Tab Panels -->
            <div class="tab-content" id="activationTabContent">
                <!-- Painel Online -->
                <div class="tab-pane fade show active" id="online-panel" role="tabpanel" aria-labelledby="online-tab">
                    <form id="onlineActivationForm" class="d-flex flex-column gap-3">
                        <div>
                            <label for="manager_url" class="form-label text-slate-300 fw-semibold mb-1" style="font-size: 0.85rem;">URL do Manager Comercial</label>
                            <input type="url" class="form-control bg-slate-900 border-slate-700 text-white placeholder-slate-500" style="border-color: rgba(63, 63, 70, 0.4) !important;" id="manager_url" placeholder="http://localhost:8080" value="{{ $defaultManagerUrl }}" required>
                        </div>
                        <div>
                            <label for="license_uuid" class="form-label text-slate-300 fw-semibold mb-1" style="font-size: 0.85rem;">UUID do Contrato de Licença</label>
                            <input type="text" class="form-control bg-slate-900 border-slate-700 text-white placeholder-slate-500 font-monospace" style="border-color: rgba(63, 63, 70, 0.4) !important;" id="license_uuid" placeholder="3ee1a5eb-fa6c-482a-a92e-3367b66f22cd" value="{{ $defaultLicenseUuid }}" required>
                        </div>
                        <button type="submit" class="btn btn-premium-primary w-100 mt-2 d-flex align-items-center justify-content-center gap-2">
                            <span class="spinner-border spinner-border-sm d-none" id="onlineSpinner" role="status" aria-hidden="true"></span>
                            <span id="onlineBtnText"><i class="bi bi-cloud-arrow-up-fill"></i> Ativar e Sincronizar Módulos</span>
                        </button>
                    </form>
                </div>

                <!-- Painel Offline -->
                <div class="tab-pane fade" id="offline-panel" role="tabpanel" aria-labelledby="offline-tab">
                    <form id="offlineActivationForm" class="d-flex flex-column gap-3">
                        <div>
                            <label for="activation_key" class="form-label text-slate-300 fw-semibold mb-1" style="font-size: 0.85rem;">Cole a Chave de Ativação / Arquivo de Licença</label>
                            <textarea class="form-control bg-slate-900 border-slate-700 text-white placeholder-slate-500 font-monospace" style="border-color: rgba(63, 63, 70, 0.4) !important;" id="activation_key" rows="6" placeholder="Cole aqui a chave de ativação obtida no Manager comercial (Base64 string ou JSON completo)..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-premium-primary w-100 mt-1 d-flex align-items-center justify-content-center gap-2">
                            <span class="spinner-border spinner-border-sm d-none" id="offlineSpinner" role="status" aria-hidden="true"></span>
                            <span id="offlineBtnText"><i class="bi bi-unlock-fill"></i> Validar e Ativar Chave</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Custom Active Tab Style Override for Premium Feel
    const tabs = document.querySelectorAll('#activationTab .nav-link');
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            tabs.forEach(t => {
                t.classList.remove('active');
                t.style.backgroundColor = 'transparent';
                t.style.color = '#a1a1aa';
            });
            e.currentTarget.classList.add('active');
            e.currentTarget.style.backgroundColor = 'rgba(6, 182, 212, 0.1)';
            e.currentTarget.style.color = '#06b6d4';
        });
    });

    // Initialize style for default active tab
    const activeTab = document.querySelector('#activationTab .nav-link.active');
    if (activeTab) {
        activeTab.style.backgroundColor = 'rgba(6, 182, 212, 0.1)';
        activeTab.style.color = '#06b6d4';
    }

    // Copiar UUID da Instalação
    const copyBtn = document.getElementById('copyUuidBtn');
    const uuidInput = document.getElementById('installationUuid');
    const copyIcon = document.getElementById('copyIcon');

    copyBtn.addEventListener('click', () => {
        uuidInput.select();
        uuidInput.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(uuidInput.value)
            .then(() => {
                copyIcon.classList.remove('bi-clipboard');
                copyIcon.classList.add('bi-check-lg');
                copyIcon.style.color = '#10b981';
                setTimeout(() => {
                    copyIcon.classList.remove('bi-check-lg');
                    copyIcon.classList.add('bi-clipboard');
                    copyIcon.style.color = '';
                }, 2000);
            })
            .catch(err => {
                console.error('Erro ao copiar UUID:', err);
            });
    });

    // Alert helper function
    const alertBox = document.getElementById('activationAlert');
    function showFeedback(message, type = 'success') {
        alertBox.className = `alert rounded-3 p-3 mb-3 border alert-${type} bg-${type}-subtle text-${type}`;
        alertBox.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i> ${message}`;
        alertBox.classList.remove('d-none');
    }

    // AJAX Ativação Online
    const onlineForm = document.getElementById('onlineActivationForm');
    const onlineSpinner = document.getElementById('onlineSpinner');
    const onlineBtnText = document.getElementById('onlineBtnText');

    onlineForm.addEventListener('submit', (e) => {
        e.preventDefault();
        alertBox.classList.add('d-none');
        onlineSpinner.classList.remove('d-none');
        onlineBtnText.style.opacity = '0.5';

        const managerUrl = document.getElementById('manager_url').value;
        const licenseUuid = document.getElementById('license_uuid').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route('admin.modules.activate-online') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                manager_url: managerUrl,
                license_uuid: licenseUuid
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, data })))
        .then(({ status, data }) => {
            onlineSpinner.classList.add('d-none');
            onlineBtnText.style.opacity = '1';

            if (status >= 400 || !data.success) {
                showFeedback(data.message || 'Erro ao realizar a ativação online.', 'danger');
            } else {
                showFeedback(data.message || 'Licença ativada com sucesso! Atualizando tela...', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        })
        .catch(err => {
            onlineSpinner.classList.add('d-none');
            onlineBtnText.style.opacity = '1';
            showFeedback('Erro de rede ou falha ao conectar no Manager comercial.', 'danger');
            console.error(err);
        });
    });

    // AJAX Ativação Offline
    const offlineForm = document.getElementById('offlineActivationForm');
    const offlineSpinner = document.getElementById('offlineSpinner');
    const offlineBtnText = document.getElementById('offlineBtnText');

    offlineForm.addEventListener('submit', (e) => {
        e.preventDefault();
        alertBox.classList.add('d-none');
        offlineSpinner.classList.remove('d-none');
        offlineBtnText.style.opacity = '0.5';

        const activationKey = document.getElementById('activation_key').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route('admin.modules.activate-offline') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                activation_key: activationKey
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, data })))
        .then(({ status, data }) => {
            offlineSpinner.classList.add('d-none');
            offlineBtnText.style.opacity = '1';

            if (status >= 400 || !data.success) {
                showFeedback(data.message || 'Erro ao processar chave offline.', 'danger');
            } else {
                showFeedback(data.message || 'Licença ativada com sucesso! Atualizando tela...', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        })
        .catch(err => {
            offlineSpinner.classList.add('d-none');
            offlineBtnText.style.opacity = '1';
            showFeedback('Erro ao processar ativação offline.', 'danger');
            console.error(err);
        });
    });
});
</script>
@endsection
