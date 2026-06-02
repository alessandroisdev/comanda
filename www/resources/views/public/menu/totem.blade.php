<!DOCTYPE html>
<html lang="pt-BR" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Totem Autoatendimento — Comanda Premium</title>
    
    <!-- Recursos compilados localmente via Vite (Bootstrap + Bootstrap Icons + Fontes) -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #020617; /* Slate 950 */
            color: #f8fafc; /* Slate 50 */
            overflow: hidden;
            user-select: none;
        }

        .totem-header {
            background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
            border-bottom: 2px solid #312e81;
            padding: 24px;
        }

        .scrollable-menu {
            height: calc(100vh - 120px);
            overflow-y: auto;
            scrollbar-width: none;
        }

        .totem-card {
            background: #0f172a;
            border: 2px solid #1e293b;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.2s;
        }
        .totem-card:active {
            transform: scale(0.98);
            border-color: #4f46e5;
        }

        .totem-sidebar {
            background: #090d16;
            border-left: 2px solid #1e293b;
            height: calc(100vh - 120px);
            display: flex;
            flex-direction: column;
        }

        .btn-totem-action {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            color: white;
            font-weight: 800;
            font-size: 1.25rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);
            padding: 20px;
            transition: all 0.2s;
        }
        .btn-totem-action:active {
            transform: translateY(2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.2);
        }

        .option-button {
            border: 2px solid #1e293b;
            background: #0f172a;
            color: #94a3b8;
            border-radius: 16px;
            padding: 20px;
            font-weight: 700;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
        }
        .option-button.active {
            border-color: #4f46e5;
            background: rgba(79, 70, 229, 0.15);
            color: white;
        }
    </style>
</head>
<body class="h-100 d-flex flex-column">

    <!-- Totem Header -->
    <header class="totem-header d-flex justify-content-between align-items-center">
        <span class="fs-3 fw-black text-white" style="letter-spacing: -0.04em;">
            <i class="bi bi-device-ssd-fill text-indigo-500 me-2"></i> COMANDA <span class="text-indigo-400">AUTOATENDIMENTO</span>
        </span>
        <div class="text-end">
            <span class="text-muted small d-block">Toque na tela para selecionar os pratos</span>
            <span class="fw-bold text-white"><i class="bi bi-clock me-1"></i> Rápido • Prático • Seguro</span>
        </div>
    </header>

    <div class="container-fluid flex-grow-1 p-0">
        <div class="row g-0 h-100">
            <!-- Menu do Totem -->
            <div class="col-md-8 scrollable-menu p-4">
                <div class="row g-4">
                    @foreach($categories as $category)
                        @if($category->products->isNotEmpty())
                            <div class="col-12 mt-3">
                                <h4 class="text-white fw-extrabold pb-2 border-bottom border-indigo-950">
                                    <i class="bi bi-bookmark-star-fill text-indigo-500 me-2"></i> {{ $category->name }}
                                </h4>
                            </div>
                            
                            @foreach($category->products as $product)
                                <div class="col-md-6 col-lg-4">
                                    <div class="totem-card p-3 h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="text-white fw-bold fs-5">{{ $product->name }}</h6>
                                            <p class="text-muted small my-2" style="line-height: 1.3;">{{ $product->description }}</p>
                                        </div>
                                        <div class="mt-4 pt-2 border-top border-slate-900 d-flex justify-content-between align-items-center">
                                            <span class="fs-4 fw-black text-success">R$ {{ number_format($product->price_cents / 100, 2, ',', '.') }}</span>
                                            <button class="btn btn-indigo btn-sm rounded-circle px-3 py-2 btn-totem-add"
                                                data-uuid="{{ $product->uuid }}"
                                                data-name="{{ $product->name }}"
                                                data-price="{{ $product->price_cents }}">
                                                <i class="bi bi-plus-lg fs-5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Carrinho do Totem -->
            <div class="col-md-4 totem-sidebar p-4">
                <h3 class="text-white fw-bold mb-3"><i class="bi bi-cart3 me-2 text-indigo-400"></i> Seu Pedido</h3>
                
                <!-- Consumo Opções -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="option-button active" id="opt-local" onclick="selectOption('local')">
                            <i class="bi bi-house-door fs-3 d-block mb-1"></i> Comer Aqui
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="option-button" id="opt-takeaway" onclick="selectOption('takeaway')">
                            <i class="bi bi-bag-check fs-3 d-block mb-1"></i> Para Levar
                        </div>
                    </div>
                </div>

                <!-- Itens -->
                <div class="flex-grow-1 overflow-y-auto mb-3" id="totem-cart-container" style="scrollbar-width: none;">
                    <div class="text-center text-muted py-5" id="totem-empty-message">
                        <i class="bi bi-emoji-smile fs-1 mb-2 d-block text-indigo-600"></i>
                        Toque no "+" ao lado dos pratos para iniciar!
                    </div>
                </div>

                <!-- Checkout -->
                <div class="border-top border-slate-800 pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Total a Pagar</span>
                        <span class="fs-3 fw-black text-success" id="totem-cart-total">R$ 0,00</span>
                    </div>

                    <button class="btn btn-totem-action w-100 py-3" id="btn-totem-confirm" disabled>
                        <i class="bi bi-receipt-cutoff me-2"></i> CONFIRMAR PEDIDO
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Senha do Pedido Fim -->
    <div class="modal fade" id="successModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-slate-900 border-2 border-indigo-500 text-white p-4 text-center rounded-4">
                <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                <h2 class="fw-black mb-2">Pedido Confirmado!</h2>
                <p class="text-muted">A cozinha já está preparando seus pratos.</p>
                <div class="bg-indigo-950 p-4 rounded-3 my-4 border border-indigo-800">
                    <span class="text-muted small d-block mb-1">SUA SENHA</span>
                    <span class="fs-1 fw-black text-indigo-400" id="ticket-senha">#042</span>
                </div>
                <p class="small text-muted mb-4">Retire seu cupom impresso no Totem e pague na saída.</p>
                <button class="btn btn-primary w-100 py-3 rounded-3" data-bs-dismiss="modal" onclick="resetTotem()">
                    NOVO PEDIDO
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts compilados localmente via Vite -->
    <script>
        let cart = [];
        let option = 'local';

        function selectOption(opt) {
            option = opt;
            document.getElementById('opt-local').classList.toggle('active', opt === 'local');
            document.getElementById('opt-takeaway').classList.toggle('active', opt === 'takeaway');
        }

        document.querySelectorAll('.btn-totem-add').forEach(btn => {
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
            const container = document.getElementById('totem-cart-container');
            const emptyMessage = document.getElementById('totem-empty-message');
            const totalVal = document.getElementById('totem-cart-total');
            const btnConfirm = document.getElementById('btn-totem-confirm');

            container.innerHTML = '';

            if (cart.length === 0) {
                emptyMessage.style.display = 'block';
                container.appendChild(emptyMessage);
                totalVal.textContent = 'R$ 0,00';
                btnConfirm.disabled = true;
                return;
            }

            emptyMessage.style.display = 'none';
            let totalCents = 0;

            cart.forEach((item, index) => {
                totalCents += item.price * item.quantity;
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between align-items-center mb-3 bg-slate-900 p-3 rounded-3 border border-indigo-950';
                row.innerHTML = `
                    <div>
                        <span class="text-white fw-bold d-block fs-5">${item.name}</span>
                        <span class="text-success fw-bold">R$ ${(item.price / 100).toFixed(2).replace('.', ',')}</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-dark btn-sm rounded-circle px-3 py-2 fs-5" onclick="changeQty(${index}, -1)">-</button>
                        <span class="text-white fw-black fs-5">${item.quantity}</span>
                        <button class="btn btn-dark btn-sm rounded-circle px-3 py-2 fs-5" onclick="changeQty(${index}, 1)">+</button>
                    </div>
                `;
                container.appendChild(row);
            });

            totalVal.textContent = `R$ ${(totalCents / 100).toFixed(2).replace('.', ',')}`;
            btnConfirm.disabled = false;
        }

        function changeQty(index, amt) {
            cart[index].quantity += amt;
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            renderCart();
        }

        document.getElementById('btn-totem-confirm').addEventListener('click', () => {
            if (cart.length === 0) return;

            fetch('/api/v1/totem/order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    option: option,
                    items: cart.map(i => ({ uuid: i.uuid, quantity: i.quantity }))
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    document.getElementById('ticket-senha').textContent = `#${res.senha}`;
                    const myModal = new bootstrap.Modal(document.getElementById('successModal'));
                    myModal.show();
                } else {
                    alert('Erro ao processar pedido no Totem: ' + res.message);
                }
            });
        });

        function resetTotem() {
            cart = [];
            selectOption('local');
            renderCart();
        }
    </script>
</body>
</html>
