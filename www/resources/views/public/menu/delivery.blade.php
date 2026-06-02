<!DOCTYPE html>
<html lang="pt-BR" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Site Delivery — Faça seu Pedido Online</title>
    
    <!-- Recursos compilados localmente via Vite (Bootstrap + Bootstrap Icons + Fontes) -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #020617; /* Slate 950 */
            color: #f8fafc; /* Slate 50 */
        }

        .delivery-header {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(2, 6, 23, 0.95) 100%);
            border-bottom: 1px solid #1e293b;
            backdrop-filter: blur(12px);
        }

        .card-delivery {
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s;
        }
        .card-delivery:hover {
            border-color: #3b82f6;
        }

        .btn-checkout-premium {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-weight: 700;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            transition: all 0.2s;
        }
        .btn-checkout-premium:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
        }

        .lgpd-box {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 16px;
        }
    </style>
</head>
<body class="h-100 d-flex flex-column">

    <!-- Header do Delivery -->
    <header class="delivery-header sticky-top py-3 px-4 d-flex justify-content-between align-items-center">
        <span class="fs-4 fw-extrabold text-white" style="letter-spacing: -0.03em;">
            <i class="bi bi-truck text-emerald-500 me-2"></i> COMANDA <span class="text-emerald-400">DELIVERY</span>
        </span>
        <button class="btn btn-outline-light rounded-pill px-4" onclick="scrollToCheckout()">
            <i class="bi bi-cart3 me-1"></i> Ver Carrinho
        </button>
    </header>

    <main class="flex-grow-1 py-5 container" style="max-width: 1200px;">
        <div class="row g-4">
            <!-- Catálogo de Produtos -->
            <div class="col-lg-7">
                <h3 class="text-white fw-bold mb-4">Cardápio Delivery</h3>
                
                <div class="row g-3">
                    @foreach($categories as $category)
                        @if($category->products->isNotEmpty())
                            <div class="col-12 mt-4">
                                <h5 class="text-emerald-400 fw-bold border-bottom border-slate-800 pb-2">{{ $category->name }}</h5>
                            </div>
                            
                            @foreach($category->products as $product)
                                <div class="col-md-6">
                                    <div class="card-delivery p-3 h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="text-white fw-bold m-0 fs-5">{{ $product->name }}</h6>
                                            <p class="text-muted small my-2" style="line-height: 1.3;">{{ $product->description }}</p>
                                        </div>
                                        <div class="mt-4 pt-2 border-top border-slate-900 d-flex justify-content-between align-items-center">
                                            <span class="fs-5 fw-bold text-success">R$ {{ number_format($product->price_cents / 100, 2, ',', '.') }}</span>
                                            <button class="btn btn-emerald btn-sm rounded-pill px-3 btn-delivery-add"
                                                data-uuid="{{ $product->uuid }}"
                                                data-name="{{ $product->name }}"
                                                data-price="{{ $product->price_cents }}">
                                                <i class="bi bi-plus-lg me-1"></i> Adicionar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Carrinho e Formuário de Checkout -->
            <div class="col-lg-5" id="delivery-checkout-section">
                <div class="card-delivery p-4">
                    <h4 class="text-white fw-bold border-bottom border-slate-800 pb-3 mb-3">
                        <i class="bi bi-bag-check-fill text-emerald-400 me-2"></i> Carrinho de Compras
                    </h4>

                    <!-- Itens -->
                    <div id="delivery-cart-container" class="mb-4">
                        <div class="text-center text-muted py-4" id="delivery-empty-msg">
                            <i class="bi bi-cart-x fs-2 mb-2 d-block"></i>
                            Carrinho vazio. Escolha pratos ao lado!
                        </div>
                    </div>

                    <!-- Dados do Destinatário & Endereço -->
                    <form id="checkout-form" class="needs-validation" novalidate>
                        <h5 class="text-white fw-bold mb-3"><i class="bi bi-person-fill text-emerald-400 me-1"></i> Seus Dados</h5>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">Nome Completo</label>
                                <input type="text" class="form-control bg-slate-900 border-slate-800 text-white rounded-3" id="cust-name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Telefone / WhatsApp</label>
                                <input type="text" class="form-control bg-slate-900 border-slate-800 text-white rounded-3" id="cust-phone" placeholder="(11) 99999-9999" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">E-mail</label>
                                <input type="email" class="form-control bg-slate-900 border-slate-800 text-white rounded-3" id="cust-email" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">CPF (Necessário para Nota Fiscal)</label>
                                <input type="text" class="form-control bg-slate-900 border-slate-800 text-white rounded-3" id="cust-cpf" placeholder="000.000.000-00" required>
                            </div>
                        </div>

                        <h5 class="text-white fw-bold mb-3"><i class="bi bi-geo-alt-fill text-emerald-400 me-1"></i> Endereço de Entrega</h5>
                        <div class="row g-2 mb-3">
                            <div class="col-md-8">
                                <label class="form-label small text-muted mb-1">CEP</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-slate-900 border-slate-800 text-white" id="addr-cep" placeholder="00000-000" required>
                                    <button class="btn btn-outline-emerald" type="button" id="btn-search-cep">Calcular Frete</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Número</label>
                                <input type="text" class="form-control bg-slate-900 border-slate-800 text-white rounded-3" id="addr-number" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">Logradouro / Rua</label>
                                <input type="text" class="form-control bg-slate-900 border-slate-800 text-white rounded-3" id="addr-street" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Bairro</label>
                                <input type="text" class="form-control bg-slate-900 border-slate-800 text-white rounded-3" id="addr-bairro" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Complemento</label>
                                <input type="text" class="form-control bg-slate-900 border-slate-800 text-white rounded-3" id="addr-complement">
                            </div>
                        </div>

                        <!-- Opção de Pagamento -->
                        <h5 class="text-white fw-bold mb-3"><i class="bi bi-credit-card-fill text-emerald-400 me-1"></i> Pagamento</h5>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="option-button active text-center p-3 border border-slate-800 rounded-3" id="pay-pix" onclick="selectPayment('pix')">
                                    <i class="bi bi-qr-code fs-4 d-block mb-1"></i> PIX Instantâneo
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="option-button text-center p-3 border border-slate-800 rounded-3" id="pay-card" onclick="selectPayment('card')">
                                    <i class="bi bi-credit-card fs-4 d-block mb-1"></i> Cartão de Crédito
                                </div>
                            </div>
                        </div>

                        <!-- Cupom de Desconto -->
                        <div class="mb-4">
                            <label class="form-label small text-muted mb-1">Cupom de Desconto</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-slate-900 border-slate-800 text-white" id="coupon-code" placeholder="Digite seu cupom">
                                <button class="btn btn-outline-success" type="button" id="btn-apply-coupon">Aplicar</button>
                            </div>
                            <span class="text-success small d-none" id="coupon-success-msg">Cupom aplicado!</span>
                        </div>

                        <!-- LGPD Compliance Checkbox -->
                        <div class="lgpd-box mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="lgpd-terms" required>
                                <label class="form-check-label small text-muted" for="lgpd-terms">
                                    Aceito os <a href="#" class="text-emerald-400">Termos de Uso</a> e autorizo o tratamento de meus dados cadastrais e de geolocalização exclusivamente para fins de faturamento e entrega deste pedido de acordo com a LGPD.
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="lgpd-marketing">
                                <label class="form-check-label small text-muted" for="lgpd-marketing">
                                    Desejo receber promoções e cupons exclusivos da Comanda no meu e-mail. (Consentimento opcional)
                                </label>
                            </div>
                        </div>

                        <!-- Totais -->
                        <div class="bg-slate-900 p-3 rounded-3 border border-slate-800 mb-4">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Itens</span>
                                <span id="summary-subtotal">R$ 0,00</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Taxa de Entrega</span>
                                <span id="summary-frete">R$ 0,00</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Desconto Cupom</span>
                                <span class="text-success" id="summary-discount">- R$ 0,00</span>
                            </div>
                            <hr class="border-slate-800">
                            <div class="d-flex justify-content-between fw-bold text-white fs-5">
                                <span>Total Geral</span>
                                <span id="summary-total">R$ 0,00</span>
                            </div>
                        </div>

                        <button class="btn btn-checkout-premium w-100 py-3 text-uppercase fw-extrabold" type="button" id="btn-delivery-submit" disabled>
                            <i class="bi bi-wallet2 me-2"></i> Finalizar e Pagar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Checkout Pagamento -->
    <div class="modal fade" id="paymentModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-slate-900 border-2 border-emerald-500 text-white p-4 text-center rounded-4">
                <i class="bi bi-qr-code text-emerald-500 fs-1 mb-2"></i>
                <h3 class="fw-black">Pagamento do Pedido</h3>
                <p class="text-muted small">Escaneie o QR Code abaixo para pagar via PIX.</p>
                <div class="bg-white p-3 rounded-3 d-inline-block my-3">
                    <img src="" id="pix-qrcode-img" style="width: 200px; height: 200px;" alt="PIX QRCode">
                </div>
                <div class="mb-3">
                    <button class="btn btn-dark btn-sm w-100" id="btn-copy-pix">Copiar Código PIX</button>
                </div>
                <p class="small text-muted mb-4"><i class="bi bi-info-circle me-1"></i> O status do seu pedido será atualizado automaticamente.</p>
                
                <!-- SSE Rastreabilidade status -->
                <div class="alert alert-info bg-slate-950 border border-slate-800 text-info text-start small">
                    <strong class="d-block mb-1"><i class="bi bi-truck me-1 text-emerald-400"></i> Status de Entrega (SSE):</strong>
                    <span id="sse-delivery-status">Aguardando pagamento...</span>
                </div>
                
                <button class="btn btn-primary w-100 py-2 rounded-3" data-bs-dismiss="modal">Fechar Rastreamento</button>
            </div>
        </div>
    </div>

    <!-- Scripts compilados localmente via Vite -->
    <script>
        let cart = [];
        let paymentMethod = 'pix';
        let freteValCents = 0;
        let discountCents = 0;
        let couponCodeValue = null;

        function scrollToCheckout() {
            document.getElementById('delivery-checkout-section').scrollIntoView({ behavior: 'smooth' });
        }

        function selectPayment(method) {
            paymentMethod = method;
            document.getElementById('pay-pix').classList.toggle('active', method === 'pix');
            document.getElementById('pay-card').classList.toggle('active', method === 'card');
        }

        document.querySelectorAll('.btn-delivery-add').forEach(btn => {
            btn.addEventListener('click', () => {
                const uuid = btn.dataset.uuid;
                const name = btn.dataset.name;
                const price = parseInt(btn.dataset.price);

                const existing = cart.find(item => item.uuid === uuid);
                if (existing) {
                    existing.quantity++;
                } else {
                    cart.push({ uuid, name, price, quantity: 1 });
                }

                renderCart();
            });
        });

        function renderCart() {
            const container = document.getElementById('delivery-cart-container');
            const emptyMsg = document.getElementById('delivery-empty-msg');
            const btnSubmit = document.getElementById('btn-delivery-submit');

            container.innerHTML = '';

            if (cart.length === 0) {
                emptyMsg.style.display = 'block';
                container.appendChild(emptyMsg);
                btnSubmit.disabled = true;
                updateSummary(0);
                return;
            }

            emptyMsg.style.display = 'none';
            let subtotal = 0;

            cart.forEach((item, index) => {
                subtotal += item.price * item.quantity;
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between align-items-center mb-3 bg-slate-900 p-3 rounded-3 border border-slate-800';
                row.innerHTML = `
                    <div>
                        <span class="text-white fw-bold d-block">${item.name}</span>
                        <span class="text-success small">R$ ${(item.price / 100).toFixed(2).replace('.', ',')}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-dark btn-sm rounded-circle px-2 btn-qty-minus" onclick="changeQty(${index}, -1)">-</button>
                        <span class="text-white fw-bold">${item.quantity}</span>
                        <button class="btn btn-dark btn-sm rounded-circle px-2 btn-qty-plus" onclick="changeQty(${index}, 1)">+</button>
                    </div>
                `;
                container.appendChild(row);
            });

            btnSubmit.disabled = false;
            updateSummary(subtotal);
        }

        function changeQty(index, amt) {
            cart[index].quantity += amt;
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            renderCart();
        }

        function updateSummary(subtotal) {
            const sumSub = document.getElementById('summary-subtotal');
            const sumFre = document.getElementById('summary-frete');
            const sumDis = document.getElementById('summary-discount');
            const sumTot = document.getElementById('summary-total');

            sumSub.textContent = `R$ ${(subtotal / 100).toFixed(2).replace('.', ',')}`;
            sumFre.textContent = `R$ ${(freteValCents / 100).toFixed(2).replace('.', ',')}`;
            sumDis.textContent = `- R$ ${(discountCents / 100).toFixed(2).replace('.', ',')}`;

            const total = Math.max(0, subtotal + freteValCents - discountCents);
            sumTot.textContent = `R$ ${(total / 100).toFixed(2).replace('.', ',')}`;
        }

        // Busca CEP & Frete
        document.getElementById('btn-search-cep').addEventListener('click', () => {
            const cep = document.getElementById('addr-cep').value;
            if (!cep) return alert('Por favor, informe o CEP!');

            fetch(`/api/v1/delivery/frete?cep=${cep}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    freteValCents = res.frete_cents;
                    document.getElementById('addr-street').value = res.logradouro || '';
                    document.getElementById('addr-bairro').value = res.bairro || '';
                    renderCart();
                } else {
                    alert('CEP não atendido pelo Delivery.');
                }
            });
        });

        // Aplicação de Cupom
        document.getElementById('btn-apply-coupon').addEventListener('click', () => {
            const code = document.getElementById('coupon-code').value;
            if (!code) return;

            let subtotal = 0;
            cart.forEach(i => subtotal += i.price * i.quantity);

            fetch(`/api/v1/coupons/validate?code=${code}&subtotal=${subtotal}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    discountCents = res.discount_cents;
                    couponCodeValue = code;
                    document.getElementById('coupon-success-msg').classList.remove('d-none');
                    renderCart();
                } else {
                    alert('Cupom inválido ou expirado.');
                }
            });
        });

        // Finalizar Checkout
        document.getElementById('btn-delivery-submit').addEventListener('click', () => {
            const termsChecked = document.getElementById('lgpd-terms').checked;
            if (!termsChecked) return alert('Você deve aceitar os termos de uso e privacidade de dados!');

            const name = document.getElementById('cust-name').value;
            const phone = document.getElementById('cust-phone').value;
            const email = document.getElementById('cust-email').value;
            const cpf = document.getElementById('cust-cpf').value;
            const cep = document.getElementById('addr-cep').value;
            const number = document.getElementById('addr-number').value;
            const street = document.getElementById('addr-street').value;
            const bairro = document.getElementById('addr-bairro').value;
            const complement = document.getElementById('addr-complement').value;

            if (!name || !phone || !email || !cpf || !cep || !number || !street || !bairro) {
                return alert('Por favor, preencha todos os campos obrigatórios!');
            }

            fetch('/api/v1/delivery/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    items: cart.map(i => ({ uuid: i.uuid, quantity: i.quantity })),
                    customer_name: name,
                    customer_phone: phone,
                    customer_email: email,
                    customer_cpf: cpf,
                    street: street,
                    number: number,
                    complement: complement,
                    neighborhood: bairro,
                    city: 'São Paulo',
                    state: 'SP',
                    zip_code: cep,
                    delivery_fee: freteValCents / 100,
                    coupon_code: couponCodeValue,
                    payment_method: paymentMethod,
                    gateway: 'asaas',
                    lgpd_consent: document.getElementById('lgpd-marketing').checked
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    document.getElementById('pix-qrcode-img').src = res.payment_data.qr_code_url || 'https://asaas.com/pix/qr/simulated';
                    document.getElementById('btn-copy-pix').onclick = () => {
                        navigator.clipboard.writeText(res.payment_data.transaction_id);
                        alert('Código PIX copiado!');
                    };
                    
                    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                    modal.show();

                    // Ativa escuta de SSE para este pedido de delivery específico
                    const eventSource = new EventSource('/sse/orders');
                    eventSource.addEventListener('order.updated', (e) => {
                        const data = JSON.parse(e.data);
                        if (data.order_uuid === res.order_uuid) {
                            document.getElementById('sse-delivery-status').textContent = `Pedido #${data.order_number} está agora: ${data.status}`;
                        }
                    });
                } else {
                    alert('Erro no checkout: ' + res.message);
                }
            });
        });
    </script>
</body>
</html>
