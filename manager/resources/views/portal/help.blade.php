@extends('layouts.portal')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 text-slate-800 fw-bold">Ajuda e Integração Técnica</h1>
        <p class="text-muted">Documentação e guia visual do ecossistema de licenciamento criptográfico RSA-2048 da Comanda.</p>
    </div>

    <span class="badge bg-primary px-3 py-2 fs-7 shadow-sm">
        <i class="bi bi-shield-lock-fill me-1"></i> RSA-2048 Ativo
    </span>
</div>

<!-- Grid Principal -->
<div class="row g-4">
    <!-- Card de Visão Geral -->
    <div class="col-12">
        <div class="card border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #f8fafc;">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="fw-bold mb-3 text-white">Como funciona o fluxo de ativação?</h2>
                        <p class="lead text-slate-300 mb-4">
                            O sistema de licenciamento utiliza criptografia assimétrica <strong>RSA-2048</strong> e assinatura física de hardware (<strong>Hardware Fingerprint</strong>) para garantir que cada instalação da Comanda seja única, auditada e imutável.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle-fill me-1"></i> Prevenção contra pirataria
                            </span>
                            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-3 py-2 rounded-pill">
                                <i class="bi bi-wifi-off me-1"></i> Resiliência 100% Offline
                            </span>
                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-3 py-2 rounded-pill">
                                <i class="bi bi-cpu-fill me-1"></i> Assinatura de Hardware
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4 d-none d-lg-block text-center">
                        <i class="bi bi-key-fill text-primary" style="font-size: 8rem; filter: drop-shadow(0 0 20px rgba(59, 130, 246, 0.4));"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Guia Visual Interativo do Fluxo -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h4 class="fw-bold text-slate-800 mb-0">Infográfico: Linha do Tempo da Licença</h4>
                <p class="text-muted mb-0">Entenda a jornada de dados do cliente até o Manager comercial.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-4 justify-content-between position-relative">
                    
                    <!-- Linha Conectora (apenas desktop) -->
                    <div class="position-absolute top-50 start-0 end-0 translate-y-middle d-none d-lg-block" style="height: 4px; background: #e2e8f0; z-index: 1; margin-top: -30px;"></div>

                    <!-- Passo 1 -->
                    <div class="col-12 col-md-6 col-lg-2 position-relative" style="z-index: 2;">
                        <div class="card h-100 border-0 bg-light p-3 text-center transition-hover shadow-sm">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px; font-weight: bold; font-size: 1.25rem;">
                                1
                            </div>
                            <h6 class="fw-bold mb-2">Emissão</h6>
                            <p class="text-muted small mb-0">O Admin gera a licença com os módulos licenciados no Manager.</p>
                        </div>
                    </div>

                    <!-- Passo 2 -->
                    <div class="col-12 col-md-6 col-lg-2 position-relative" style="z-index: 2;">
                        <div class="card h-100 border-0 bg-light p-3 text-center transition-hover shadow-sm">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px; font-weight: bold; font-size: 1.25rem;">
                                2
                            </div>
                            <h6 class="fw-bold mb-2">Identificação</h6>
                            <p class="text-muted small mb-0">O Cliente gera o <code>installation_uuid</code> e a assinatura de Hardware.</p>
                        </div>
                    </div>

                    <!-- Passo 3 -->
                    <div class="col-12 col-md-6 col-lg-2 position-relative" style="z-index: 2;">
                        <div class="card h-100 border-0 bg-light p-3 text-center transition-hover shadow-sm">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px; font-weight: bold; font-size: 1.25rem;">
                                3
                            </div>
                            <h6 class="fw-bold mb-2">Ativação</h6>
                            <p class="text-muted small mb-0">O Cliente envia os dados via API HTTPS para ativar o serviço.</p>
                        </div>
                    </div>

                    <!-- Passo 4 -->
                    <div class="col-12 col-md-6 col-lg-2 position-relative" style="z-index: 2;">
                        <div class="card h-100 border-0 bg-light p-3 text-center transition-hover shadow-sm">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px; font-weight: bold; font-size: 1.25rem;">
                                4
                            </div>
                            <h6 class="fw-bold mb-2">Assinatura</h6>
                            <p class="text-muted small mb-0">O Manager valida o host e assina a licença com a Chave Privada RSA.</p>
                        </div>
                    </div>

                    <!-- Passo 5 -->
                    <div class="col-12 col-md-6 col-lg-2 position-relative" style="z-index: 2;">
                        <div class="card h-100 border-0 bg-light p-3 text-center transition-hover shadow-sm">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px; font-weight: bold; font-size: 1.25rem;">
                                5
                            </div>
                            <h6 class="fw-bold mb-2">Operação</h6>
                            <p class="text-muted small mb-0">O Cliente salva <code>license.json</code> local e opera offline seguro.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Blocos de Conteúdo e Código de Integração -->
    <div class="col-lg-8">
        <!-- Detalhes Técnicos dos Passos -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h4 class="fw-bold text-slate-800 mb-3"><i class="bi bi-gear-wide-connected text-primary me-2"></i>Guia de Implementação e Arquitetura</h4>
                
                <h5 class="fw-bold mt-4 text-slate-700">1. Configuração do Cliente (.env)</h5>
                <p class="text-slate-600">
                    O aplicativo cliente (`www`) precisa saber onde o Manager comercial está escutando para enviar os dados de ativação física. Adicione as seguintes configurações no seu arquivo <code>.env</code> do cliente:
                </p>
                <div class="bg-dark text-light p-3 rounded mb-3">
                    <pre class="mb-0" style="font-family: monospace; font-size: 0.875rem;">
# Configuração de Licenciamento
LICENSE_MANAGER_URL=http://localhost:8080
LICENSE_UUID=3ee1a5eb-fa6c-482a-a92e-3367b66f22cd</pre>
                </div>

                <h5 class="fw-bold mt-4 text-slate-700">2. Endpoint de Ativação Online</h5>
                <p class="text-slate-600">
                    Para registrar e ativar a licença física do hardware, o cliente deve realizar uma chamada HTTPS para o Manager comercial.
                </p>
                
                <div class="table-responsive border rounded mb-3">
                    <table class="table table-sm align-middle mb-0" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Método</th>
                                <th>Rota</th>
                                <th>Autenticação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/licenses/activate</code></td>
                                <td>Nenhuma (Usa UUID do Contrato)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold text-slate-700">Payload Enviado pelo Cliente:</h6>
                <div class="bg-dark text-light p-3 rounded mb-3">
                    <pre class="mb-0" style="font-family: monospace; font-size: 0.85rem;">{
  "license_uuid": "3ee1a5eb-fa6c-482a-a92e-3367b66f22cd",
  "installation_uuid": "f2a24bc1-5369-42b7-bd20-1e5b22b109cc",
  "hostname": "caixa-terminal-01",
  "ip_address": "192.168.1.15",
  "domain": "comanda.local",
  "fingerprint": "a598f8280f9e1e2d93e506927d6cd5e2f7b889d1b09b0c9e6"
}</pre>
                </div>

                <h6 class="fw-bold text-slate-700">Resposta de Sucesso do Manager:</h6>
                <p class="text-slate-600 text-sm">
                    A licença retornada no campo <code>license_key</code> é um payload assinado digitalmente em Base64 contendo os metadados validados pelo Manager.
                </p>
                <div class="bg-dark text-light p-3 rounded">
                    <pre class="mb-0" style="font-family: monospace; font-size: 0.85rem;">{
  "status": "success",
  "message": "Licença física ativada com sucesso!",
  "license_key": "eyJsaWNlbnNlX3V1aWQiOiIzZWUxYTVlYi1mYTZjLTQ4MmEtYTkyZS0z...[Assinatura RSA-2048]"
}</pre>
                </div>
            </div>
        </div>

        <!-- Exemplo de Código do Validador -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold text-slate-800 mb-3"><i class="bi bi-code-slash text-primary me-2"></i>Como a Validação é Executada</h4>
                <p class="text-slate-600">
                    O validador local de licenças do cliente (`LicenseValidator.php`) executa a decodificação da chave local usando a chave pública do Manager, verificando a integridade e garantindo que o fingerprint coincide com a máquina atual.
                </p>
                <div class="bg-dark text-light p-3 rounded">
                    <pre class="mb-0" style="font-family: monospace; font-size: 0.8rem; overflow-x: auto;"><span class="text-warning">&lt;?php</span>

<span class="text-info">namespace</span> App\Services\Licensing;

<span class="text-info">class</span> <span class="text-success">LicenseValidator</span>
{
    <span class="text-info">public function</span> <span class="text-primary">validateLocalLicense</span>(<span class="text-info">string</span> <span class="text-danger">$licenseJsonPath</span>, <span class="text-info">string</span> <span class="text-danger">$publicKeyPath</span>): <span class="text-info">bool</span>
    {
        <span class="text-muted">// 1. Verifica se o arquivo local de licença existe</span>
        <span class="text-info">if</span> (!file_exists(<span class="text-danger">$licenseJsonPath</span>)) {
            <span class="text-info">return false</span>;
        }

        <span class="text-danger">$data</span> = json_decode(file_get_contents(<span class="text-danger">$licenseJsonPath</span>), <span class="text-info">true</span>);
        <span class="text-danger">$licenseKey</span> = <span class="text-danger">$data</span>[<span class="text-success">'license_key'</span>] ?? <span class="text-success">''</span>;

        <span class="text-muted">// 2. Decodifica e valida a assinatura RSA-2048 usando a chave pública</span>
        <span class="text-danger">$publicKey</span> = openssl_pkey_get_public(file_get_contents(<span class="text-danger">$publicKeyPath</span>));
        
        <span class="text-muted">// Divide a payload e a assinatura criptográfica original</span>
        list(<span class="text-danger">$payload</span>, <span class="text-danger">$signature</span>) = explode(<span class="text-success">'.'</span>, <span class="text-danger">$licenseKey</span>);

        <span class="text-danger">$isValid</span> = openssl_verify(
            base64_decode(<span class="text-danger">$payload</span>),
            base64_decode(<span class="text-danger">$signature</span>),
            <span class="text-danger">$publicKey</span>,
            OPENSSL_ALGO_SHA256
        );

        <span class="text-info">if</span> (<span class="text-danger">$isValid</span> !== <span class="text-success">1</span>) {
            <span class="text-info">return false</span>; <span class="text-muted">// Assinatura corrompida ou inválida</span>
        }

        <span class="text-danger">$payloadData</span> = json_decode(base64_decode(<span class="text-danger">$payload</span>), <span class="text-info">true</span>);

        <span class="text-muted">// 3. Valida se o Hardware Fingerprint bate com o host físico atual</span>
        <span class="text-danger">$currentFingerprint</span> = <span class="text-info">this</span>->generateSystemFingerprint();
        <span class="text-info">if</span> (<span class="text-danger">$payloadData</span>[<span class="text-success">'fingerprint'</span>] !== <span class="text-danger">$currentFingerprint</span>) {
            <span class="text-info">return false</span>; <span class="text-muted">// Licença copiada para outra máquina física!</span>
        }

        <span class="text-info">return true</span>;
    }
}</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Coluna Lateral de Resumo e Segurança -->
    <div class="col-lg-4">
        <!-- Card de Resiliência Offline -->
        <div class="card border-0 shadow-sm mb-4" style="background-color: #eff6ff;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded p-2 me-3">
                        <i class="bi bi-wifi-off fs-4"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-primary">Tolerância Offline</h5>
                </div>
                <p class="text-slate-700 small">
                    A Comanda foi desenhada para operar de forma resiliente em ambientes comerciais complexos (onde a rede de internet pode falhar).
                </p>
                <div class="bg-white p-3 rounded border border-primary border-opacity-25">
                    <h6 class="fw-bold text-dark mb-1">Período de Graça: 7 Dias</h6>
                    <p class="text-muted small mb-0">
                        O cliente armazena um timestamp de sincronização criptografado no arquivo local. Se o Manager comercial ficar indisponível, o cliente permite operação offline por até 7 dias sem interrupções.
                    </p>
                </div>
            </div>
        </div>

        <!-- Card de Proteção de Hardware -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-dark text-white rounded p-2 me-3">
                        <i class="bi bi-cpu fs-4"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-slate-800">Hardware Fingerprint</h5>
                </div>
                <p class="text-slate-600 small">
                    A assinatura de hardware é calculada de forma determinística em cada inicialização do sistema, inspecionando de forma combinada:
                </p>
                <ul class="list-unstyled mb-0" style="font-size: 0.875rem;">
                    <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>UUID da Placa Mãe (DMI)</li>
                    <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>UUID do Processador (CPU ID)</li>
                    <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>MAC Address da interface primária</li>
                    <li class="mb-0"><i class="bi bi-check-lg text-success me-2"></i>Capacidade física do disco de boot</li>
                </ul>
            </div>
        </div>

        <!-- Card de Alertas Importantes -->
        <div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger">
            <div class="card-body p-4">
                <h5 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Aviso aos Administradores</h5>
                <p class="text-slate-600 small mb-0">
                    Nunca exponha a **Chave Privada** do Manager comercial (`storage/keys/private.key`). Ela é usada pelo Manager para assinar novas ativações. Apenas a **Chave Pública** correspondente deve ser embutida ou distribuída nos clientes.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .transition-hover {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .transition-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }
</style>
@endsection
