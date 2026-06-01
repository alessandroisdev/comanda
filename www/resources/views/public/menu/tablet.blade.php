<!DOCTYPE html>
<html lang="pt-BR" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Mesa {{ $table->name }} — Menu Executivo Tablet</title>
    
    <!-- Bootstrap 5 CSS e Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #020617; /* Slate 950 */
            color: #f8fafc; /* Slate 50 */
            overflow: hidden;
        }

        .premium-header {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(2, 6, 23, 0.9) 100%);
            border-bottom: 1px solid #1e293b;
            backdrop-filter: blur(15px);
        }

        .scrollable-content {
            height: calc(100vh - 85px);
            overflow-y: auto;
            scrollbar-width: none;
        }

        .card-menu {
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s;
        }
        .card-menu:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
        }

        .sidebar-cart {
            background: #0b0f19;
            border-left: 1px solid #1e293b;
            height: calc(100vh - 85px);
            display: flex;
            flex-direction: column;
        }

        .btn-premium {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border: none;
            color: white;
            font-weight: 700;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            transition: all 0.2s;
        }
        .btn-premium:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.45);
        }

        .badge-table {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
            font-weight: 700;
            border-radius: 30px;
            padding: 6px 16px;
        }

        .sse-notify {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 3000;
            background: #1e293b;
            border: 1px solid #3b82f6;
            border-radius: 12px;
            padding: 16px;
            max-width: 320px;
            display: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="h-100 d-flex flex-column">

    <!-- Header Fixo Premium -->
    <header class="premium-header py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span class="fs-4 fw-extrabold text-white" style="letter-spacing: -0.03em;">
                <i class="bi bi-tablet-landscape text-primary me-2"></i> COMANDA <span class="text-primary">TABLET</span>
            </span>
            <span class="badge-table">
                <i class="bi bi-geo-alt-fill me-1"></i> Mesa {{ $table->name }}
            </span>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-outline-warning rounded-pill px-4" id="btn-tablet-waiter">
                <i class="bi bi-bell-fill me-1"></i> Chamar Atendente
            </button>
            <button class="btn btn-outline-info rounded-pill px-4" id="btn-tablet-bill">
                <i class="bi bi-receipt-cutoff me-1"></i> Fechar Conta
            </button>
        </div>
    </header>

    <div class="container-fluid flex-grow-1 p-0">
        <div class="row g-0 h-100">
            <!-- Coluna de Produtos Cardápio -->
            <div class="col-md-8 scrollable-content p-4">
                <h3 class="text-white fw-bold mb-4">Escolha os pratos para sua mesa</h3>
                
                <div class="row g-4">
                    @foreach($categories as $category)
                        @if($category->products->isNotEmpty())
                            <div class="col-12 mt-4">
                                <h5 class="text-secondary border-bottom border-slate-800 pb-2">{{ $category->name }}</h5>
                            </div>
                            
                            @foreach($category->products as $product)
                                <div class="col-md-6">
                                    <div class="card-menu d-flex p-3 h-100">
                                        <div class="flex-grow-1">
                                            <h6 class="text-white fw-bold m-0">{{ $product->name }}</h6>
                                            <p class="text-muted small my-2" style="line-height: 1.3;">{{ $product->description }}</p>
                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-slate-900">
                                                <span class="fs-6 fw-bold text-success">R$ {{ number_format($product->price_cents / 100, 2, ',', '.') }}</span>
                                                <button class="btn btn-primary btn-sm rounded-pill btn-add-item" 
                                                    data-uuid="{{ $product->uuid }}" 
                                                    data-name="{{ $product->name }}" 
                                                    data-price="{{ $product->price_cents }}">
                                                    <i class="bi bi-plus-lg me-1"></i> Adicionar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Coluna do Carrinho de Compras da Mesa -->
            <div class="col-md-4 sidebar-cart p-4">
                <h4 class="text-white fw-bold border-bottom border-slate-800 pb-3 mb-3">
                    <i class="bi bi-cart4 text-primary me-2"></i> Carrinho da Mesa
                </h4>

                <!-- Itens do Carrinho -->
                <div class="flex-grow-1 overflow-y-auto mb-3" id="cart-items-container" style="scrollbar-width: none;">
                    <div class="text-center text-muted py-5" id="cart-empty-message">
                        <i class="bi bi-cart-x fs-1 mb-2 d-block"></i>
                        Seu carrinho está vazio
                    </div>
                </div>

                <!-- Resumo e Envio -->
                <div class="border-top border-slate-800 pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Subtotal</span>
                        <span class="fs-4 fw-bold text-success" id="cart-total-value">R$ 0,00</span>
                    </div>

                    <button class="btn btn-premium w-100 py-3" id="btn-tablet-checkout" disabled>
                        <i class="bi bi-check-circle-fill me-2"></i> Enviar Pedido para Cozinha
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Caixa de Notificação SSE -->
    <div class="sse-notify" id="sse-notification">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-info-circle-fill text-primary fs-5"></i>
            <strong class="text-white">Status do Pedido</strong>
        </div>
        <span class="text-muted small" id="sse-notification-msg">O status do seu pedido mudou.</span>
    </div>

    <!-- Scripts de Reatividade, Offline e SSE -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module">
        document.addEventListener('DOMContentLoaded', () => {
            const tableUuid = "{{ $table->public_uuid }}";
            let cart = [];

            // 1. Ações de Chamar Garçom e Conta
            document.getElementById('btn-tablet-waiter')?.addEventListener('click', () => {
                fetch(`/api/v1/tables/${tableUuid}/call-waiter`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        alert('Garçom acionado! Um atendente virá à mesa.');
                    }
                });
            });

            document.getElementById('btn-tablet-bill')?.addEventListener('click', () => {
                if (confirm('Deseja fechar sua conta? A cozinha será notificada para encerramento.')) {
                    fetch(`/api/v1/tables/${tableUuid}/request-bill`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            alert('Conta solicitada com sucesso! Aguarde o cupom para pagamento.');
                        }
                    });
                }
            });

            // 2. Lógica de Adicionar Itens ao Carrinho
            document.querySelectorAll('.btn-add-item').forEach(btn => {
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
                const container = document.getElementById('cart-items-container');
                const emptyMessage = document.getElementById('cart-empty-message');
                const totalVal = document.getElementById('cart-total-value');
                const btnCheckout = document.getElementById('btn-tablet-checkout');

                container.innerHTML = '';

                if (cart.length === 0) {
                    emptyMessage.style.display = 'block';
                    container.appendChild(emptyMessage);
                    totalVal.textContent = 'R$ 0,00';
                    btnCheckout.disabled = true;
                    return;
                }

                emptyMessage.style.display = 'none';
                let totalCents = 0;

                cart.forEach((item, index) => {
                    totalCents += item.price * item.quantity;
                    const row = document.createElement('div');
                    row.className = 'd-flex justify-content-between align-items-center mb-3 bg-slate-900 p-3 rounded-3 border border-slate-800';
                    row.innerHTML = `
                        <div>
                            <span class="text-white fw-bold d-block">${item.name}</span>
                            <span class="text-success small">R$ ${(item.price / 100).toFixed(2).replace('.', ',')}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-dark btn-sm rounded-circle px-2 btn-qty-minus" data-index="${index}">-</button>
                            <span class="text-white fw-bold">${item.quantity}</span>
                            <button class="btn btn-dark btn-sm rounded-circle px-2 btn-qty-plus" data-index="${index}">+</button>
                        </div>
                    `;
                    container.appendChild(row);
                });

                totalVal.textContent = `R$ ${(totalCents / 100).toFixed(2).replace('.', ',')}`;
                btnCheckout.disabled = false;

                // Registrar eventos de botões do carrinho
                document.querySelectorAll('.btn-qty-minus').forEach(b => {
                    b.addEventListener('click', () => {
                        const index = parseInt(b.dataset.index);
                        cart[index].quantity--;
                        if (cart[index].quantity <= 0) {
                            cart.splice(index, 1);
                        }
                        renderCart();
                    });
                });

                document.querySelectorAll('.btn-qty-plus').forEach(b => {
                    b.addEventListener('click', () => {
                        const index = parseInt(b.dataset.index);
                        cart[index].quantity++;
                        renderCart();
                    });
                });
            }

            // 3. Checkout do Tablet para Cozinha
            document.getElementById('btn-tablet-checkout')?.addEventListener('click', () => {
                if (cart.length === 0) return;

                fetch('/api/v1/tablet/order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        table_uuid: tableUuid,
                        items: cart.map(i => ({ uuid: i.uuid, quantity: i.quantity }))
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        alert('Pedido enviado à cozinha com sucesso!');
                        cart = [];
                        renderCart();
                    } else {
                        alert('Erro ao enviar pedido: ' + res.message);
                    }
                });
            });

            // 4. Integração Realtime SSE para Notificações do Pedido da Mesa
            const sseNotify = document.getElementById('sse-notification');
            const sseNotifyMsg = document.getElementById('sse-notification-msg');

            const eventSource = new EventSource('/sse/tables');
            eventSource.addEventListener('order.updated', (e) => {
                const data = JSON.parse(e.data);
                if (data.table_uuid === tableUuid) {
                    sseNotifyMsg.textContent = `Pedido #${data.order_number} está agora: ${data.status}`;
                    sseNotify.style.display = 'block';
                    setTimeout(() => {
                        sseNotify.style.display = 'none';
                    }, 5000);
                }
            });
        });
    </script>
</body>
</html>
