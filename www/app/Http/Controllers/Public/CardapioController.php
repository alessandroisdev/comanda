<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Actions\Payment\ProcessWebhookAction;
use App\DTOs\Payment\PaymentRequestDTO;
use App\Enums\CustomerStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSession;
use App\Models\PrivacyAuditLog;
use App\Models\Product;
use App\Models\Table;
use App\Services\Payment\GatewayManager;
use App\Services\Qrcode\QrCodeService;
use App\Services\SSE\SseQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CardapioController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodeService,
        private readonly GatewayManager $gatewayManager
    ) {}

    /**
     * Exibe o cardápio digital público, com suporte a deep link de mesa via slug.
     */
    public function index(Request $request, ?string $slug = null)
    {
        $table = null;
        if ($slug) {
            $table = Table::where('slug', $slug)->first();
            if ($table instanceof Table) {
                session([
                    'public_table_uuid' => $table->public_uuid,
                    'company_id' => $table->company_id,
                    'unit_id' => $table->unit_id,
                ]);
            }
        }

        $companyId = ($table instanceof Table) ? $table->company_id : $request->get('company_id', 1);
        $cacheKey = "menu_public:{$companyId}";

        try {
            $categories = Cache::remember($cacheKey, 600, function () use ($companyId) {
                return Category::where('company_id', $companyId)
                    ->where('status', 'active')
                    ->with(['products' => function ($q) {
                        $q->where('status', 'active')->orderBy('name');
                    }])
                    ->orderBy('sort_order')
                    ->get();
            });
        } catch (\Throwable $e) {
            // Fallback silencioso direto do banco de dados caso o Redis esteja offline
            $categories = Category::where('company_id', $companyId)
                ->where('status', 'active')
                ->with(['products' => function ($q) {
                    $q->where('status', 'active')->orderBy('name');
                }])
                ->orderBy('sort_order')
                ->get();
        }

        $seo = [
            'title' => ($table instanceof Table) ? "Mesa {$table->name} — Cardápio Digital" : 'Cardápio Digital Oficial',
            'description' => 'Explore nosso catálogo digital completo, faça seus pedidos e aproveite nossos combos e promoções exclusivas.',
            'canonical' => ($table instanceof Table) ? route('public.menu.table', ['slug' => $table->slug]) : route('public.menu'),
            'image' => asset('/js/icon-512.png'),
        ];

        return view('public.menu.index', compact('categories', 'table', 'seo'));
    }

    /**
     * Exibe o Tablet de Mesa.
     */
    public function tablet(Request $request, string $publicUuid)
    {
        /** @var Table $table */
        $table = Table::where('public_uuid', $publicUuid)->firstOrFail();

        // Coloca a mesa como ocupada ao abrir o tablet
        if ($table->status === TableStatusEnum::AVAILABLE) {
            $table->update(['status' => 'occupied']);
            SseQueueService::publish('admin.tables', 'table.updated', [
                'table_uuid' => $table->uuid,
                'status' => 'occupied',
            ]);
        }

        $companyId = $table->company_id;
        $categories = Category::where('company_id', $companyId)
            ->where('status', 'active')
            ->with(['products' => function ($q) {
                $q->where('status', 'active')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('public.menu.tablet', compact('categories', 'table'));
    }

    /**
     * Exibe o Totem de Autoatendimento.
     */
    public function totem(Request $request)
    {
        $companyId = $request->get('company_id', 1);
        $categories = Category::where('company_id', $companyId)
            ->where('status', 'active')
            ->with(['products' => function ($q) {
                $q->where('status', 'active')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('public.menu.totem', compact('categories'));
    }

    /**
     * Exibe o Site Delivery.
     */
    public function delivery(Request $request)
    {
        $companyId = $request->get('company_id', 1);
        $categories = Category::where('company_id', $companyId)
            ->where('status', 'active')
            ->with(['products' => function ($q) {
                $q->where('status', 'active')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('public.menu.delivery', compact('categories'));
    }

    /**
     * Valida um cupom via AJAX.
     */
    public function validateCoupon(Request $request)
    {
        $code = $request->get('code');
        $subtotalCents = (int) $request->get('subtotal', 0);
        $companyId = (int) $request->get('company_id', 1);

        /** @var Coupon $coupon */
        $coupon = Coupon::where('code', $code)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Cupom não encontrado ou inativo.']);
        }

        $discount = $coupon->calculateDiscount($subtotalCents);

        if ($discount <= 0) {
            return response()->json(['success' => false, 'message' => 'Condições mínimas do cupom não atendidas ou cupom expirado.']);
        }

        return response()->json([
            'success' => true,
            'discount_cents' => $discount,
            'code' => $coupon->code,
        ]);
    }

    /**
     * Calcula o frete baseado no CEP.
     */
    public function calculateFrete(Request $request)
    {
        $cep = $request->get('cep');
        if (empty($cep)) {
            return response()->json(['success' => false, 'message' => 'CEP inválido.']);
        }

        // Mock ViaCEP integrado
        $freteCents = 1000; // Taxa padrão R$ 10,00

        return response()->json([
            'success' => true,
            'frete_cents' => $freteCents,
            'logradouro' => 'Avenida Paulista',
            'bairro' => 'Bela Vista',
            'localidade' => 'São Paulo',
            'uf' => 'SP',
        ]);
    }

    /**
     * Processa o pedido enviado pelo tablet na mesa física.
     */
    public function tabletOrder(Request $request)
    {
        $tableUuid = $request->json('table_uuid');
        $items = $request->json('items', []);

        /** @var Table $table */
        $table = Table::where('public_uuid', $tableUuid)->firstOrFail();

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Seu carrinho está vazio.']);
        }

        foreach ($items as $itemData) {
            if (! isset($itemData['quantity']) || (int) $itemData['quantity'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'A quantidade de cada item deve ser maior que zero.',
                    'errors' => ['items' => ['A quantidade de cada item deve ser maior que zero.']],
                ], 422);
            }
        }

        $result = DB::transaction(function () use ($table, $items) {
            // Cria ou localiza uma sessão de comanda ativa para a mesa
            $session = OrderSession::where('company_id', $table->company_id)
                ->where('unit_id', $table->unit_id)
                ->where('table_id', $table->id)
                ->where('status', 'open')
                ->first();

            // Pega o primeiro funcionário da empresa para associar ao pedido
            $employee = Employee::where('company_id', $table->company_id)->first();
            if (! $employee) {
                return ['success' => false, 'message' => 'Nenhum atendente disponível no estabelecimento.'];
            }

            if (! $session) {
                $session = OrderSession::create([
                    'company_id' => $table->company_id,
                    'unit_id' => $table->unit_id,
                    'table_id' => $table->id,
                    'opened_by_employee_id' => $employee->id,
                    'people_count' => 1,
                    'status' => 'open',
                    'opened_at' => now(),
                ]);
            }

            // Cria o pedido principal
            $order = Order::create([
                'company_id' => $table->company_id,
                'unit_id' => $table->unit_id,
                'session_id' => $session->id,
                'employee_id' => $employee->id,
                'order_number' => 'TBL-'.strtoupper(bin2hex(random_bytes(3))),
                'status' => OrderStatusEnum::PENDING,
            ]);

            $subtotalCents = 0;
            foreach ($items as $itemData) {
                /** @var Product $product */
                $product = Product::where('uuid', $itemData['uuid'])->first();
                if ($product) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity'],
                        'unit_price_cents' => $product->price_cents,
                        'total_price_cents' => $product->price_cents * $itemData['quantity'],
                    ]);
                    $subtotalCents += $product->price_cents * $itemData['quantity'];
                }
            }

            $order->update([
                'subtotal_cents' => $subtotalCents,
                'total_cents' => $subtotalCents,
            ]);

            // Enfileira reativamente via SSE para painel da cozinha e garçom
            SseQueueService::publish('admin.orders', 'order.created', [
                'order_uuid' => $order->uuid,
                'order_number' => $order->order_number,
                'table_name' => $table->name,
                'status' => 'pending',
            ]);

            return ['success' => true, 'order_uuid' => $order->uuid];
        });

        return response()->json($result);
    }

    /**
     * Processa o checkout do Totem.
     */
    public function checkoutTotem(Request $request)
    {
        $items = $request->json('items', []);
        $option = $request->json('option', 'local');

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Carrinho vazio.']);
        }

        foreach ($items as $itemData) {
            if (! isset($itemData['quantity']) || (int) $itemData['quantity'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'A quantidade de cada item deve ser maior que zero.',
                    'errors' => ['items' => ['A quantidade de cada item deve ser maior que zero.']],
                ], 422);
            }
        }

        $result = DB::transaction(function () use ($items) {
            $companyId = 1;
            $employee = Employee::where('company_id', $companyId)->first();
            if (! $employee) {
                return ['success' => false, 'message' => 'Totem inoperante: sem funcionários registrados.'];
            }

            // Totem cria sua própria sessão de consumo rápido
            $session = OrderSession::create([
                'company_id' => $companyId,
                'unit_id' => $employee->unit_id ?? 1,
                'opened_by_employee_id' => $employee->id,
                'people_count' => 1,
                'status' => 'open',
                'opened_at' => now(),
            ]);

            $senha = str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT);

            $order = Order::create([
                'company_id' => $companyId,
                'unit_id' => $employee->unit_id ?? 1,
                'session_id' => $session->id,
                'employee_id' => $employee->id,
                'order_number' => 'TOT-'.$senha,
                'status' => OrderStatusEnum::PENDING,
            ]);

            $subtotalCents = 0;
            foreach ($items as $itemData) {
                /** @var Product $product */
                $product = Product::where('uuid', $itemData['uuid'])->first();
                if ($product) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity'],
                        'unit_price_cents' => $product->price_cents,
                        'total_price_cents' => $product->price_cents * $itemData['quantity'],
                    ]);
                    $subtotalCents += $product->price_cents * $itemData['quantity'];
                }
            }

            $order->update([
                'subtotal_cents' => $subtotalCents,
                'total_cents' => $subtotalCents,
            ]);

            // SSE para Cozinha
            SseQueueService::publish('admin.orders', 'order.created', [
                'order_uuid' => $order->uuid,
                'order_number' => $order->order_number,
                'table_name' => 'Totem Autoatendimento',
                'status' => 'pending',
            ]);

            return ['success' => true, 'senha' => $senha];
        });

        return response()->json($result);
    }

    /**
     * Processa o checkout completo do Delivery com gateway desacoplado e privacidade LGPD.
     */
    public function checkoutDelivery(Request $request)
    {
        $items = $request->json('items', []);
        $custName = $request->json('customer_name');
        $custPhone = $request->json('customer_phone');
        $custEmail = $request->json('customer_email');
        $custCpf = $request->json('customer_cpf');
        $street = $request->json('street');
        $number = $request->json('number');
        $complement = $request->json('complement');
        $neighborhood = $request->json('neighborhood');
        $city = $request->json('city');
        $state = $request->json('state');
        $zipCode = $request->json('zip_code');
        $deliveryFeeVal = (float) $request->json('delivery_fee', 0);
        $couponCode = $request->json('coupon_code');
        $paymentMethod = $request->json('payment_method', 'pix');
        $gatewayName = $request->json('gateway', 'asaas');
        $lgpdConsent = (bool) $request->json('lgpd_consent', false);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Carrinho vazio.']);
        }

        foreach ($items as $itemData) {
            if (! isset($itemData['quantity']) || (int) $itemData['quantity'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'A quantidade de cada item deve ser maior que zero.',
                    'errors' => ['items' => ['A quantidade de cada item deve ser maior que zero.']],
                ], 422);
            }
        }

        $result = DB::transaction(function () use (
            $items, $custName, $custPhone, $custEmail, $custCpf,
            $street, $number, $complement, $neighborhood, $city, $state, $zipCode,
            $deliveryFeeVal, $couponCode, $paymentMethod, $gatewayName, $lgpdConsent
        ) {
            $companyId = request()->json('company_id') ?? request()->get('company_id') ?? 1;
            $employee = Employee::query()->where('company_id', $companyId)->first();
            if (! $employee) {
                $employee = Employee::query()->first();
                if ($employee) {
                    $companyId = $employee->company_id;
                }
            }
            if (! $employee) {
                return ['success' => false, 'message' => 'Delivery inoperante: sem funcionários.'];
            }

            // 1. Busca ou cria o cliente com conformidade LGPD plena
            /** @var Customer $customer */
            $customer = Customer::where('document', $custCpf)->first();
            if (! $customer) {
                $customer = Customer::create([
                    'company_id' => $companyId,
                    'name' => $custName,
                    'email' => $custEmail,
                    'phone' => $custPhone,
                    'document' => $custCpf,
                    'password' => Hash::make(Str::random(40)),
                    'status' => CustomerStatusEnum::ACTIVE,
                ]);
            }

            // Registra base legal de execução de contrato (dados obrigatórios para faturamento/entrega)
            PrivacyAuditLog::create([
                'company_id' => $companyId,
                'entity_type' => 'Customer',
                'entity_uuid' => $customer->uuid,
                'action' => 'privacy.legal_basis',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Se o consentimento opcional de marketing foi concedido
            if ($lgpdConsent) {
                PrivacyAuditLog::create([
                    'company_id' => $companyId,
                    'entity_type' => 'Customer',
                    'entity_uuid' => $customer->uuid,
                    'action' => 'privacy.consent_granted',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            // 2. Cria comanda/sessão de delivery temporária
            $session = OrderSession::create([
                'company_id' => $companyId,
                'unit_id' => $employee->unit_id ?? 1,
                'opened_by_employee_id' => $employee->id,
                'people_count' => 1,
                'status' => 'open',
                'opened_at' => now(),
            ]);

            // 3. Cria a ordem física
            $order = Order::create([
                'company_id' => $companyId,
                'unit_id' => $employee->unit_id ?? 1,
                'session_id' => $session->id,
                'employee_id' => $employee->id,
                'order_number' => 'DEL-'.strtoupper(bin2hex(random_bytes(3))),
                'status' => OrderStatusEnum::DRAFT, // Fica como draft até o gateway confirmar pagamento
            ]);

            $subtotalCents = 0;
            foreach ($items as $itemData) {
                /** @var Product $product */
                $product = Product::where('uuid', $itemData['uuid'])->first();
                if ($product) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity'],
                        'unit_price_cents' => $product->price_cents,
                        'total_price_cents' => $product->price_cents * $itemData['quantity'],
                    ]);
                    $subtotalCents += $product->price_cents * $itemData['quantity'];
                }
            }

            // 4. Lógica de Cupom de Desconto
            $discountCents = 0;
            $couponId = null;
            if ($couponCode) {
                /** @var Coupon $coupon */
                $coupon = Coupon::where('code', $couponCode)
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->first();

                if ($coupon) {
                    $discountCents = $coupon->calculateDiscount($subtotalCents);
                    if ($discountCents > 0) {
                        $couponId = $coupon->id;
                        $coupon->used_count++;
                        $coupon->save();
                    }
                }
            }

            $deliveryFeeCents = (int) ($deliveryFeeVal * 100);
            $totalCents = max(0, $subtotalCents + $deliveryFeeCents - $discountCents);

            $order->update([
                'subtotal_cents' => $subtotalCents,
                'discount_cents' => $discountCents,
                'coupon_id' => $couponId,
                'discount_amount_cents' => $discountCents,
                'total_cents' => $totalCents,
            ]);

            // 5. Registra o DeliveryOrder associado
            $deliveryOrder = DeliveryOrder::create([
                'company_id' => $companyId,
                'unit_id' => $employee->unit_id ?? 1,
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'recipient_name' => $custName,
                'recipient_phone' => $custPhone,
                'street' => $street,
                'number' => $number,
                'complement' => $complement,
                'neighborhood' => $neighborhood,
                'city' => $city,
                'state' => $state,
                'zip_code' => $zipCode,
                'delivery_fee' => $deliveryFeeVal,
                'status' => 'pending',
            ]);

            // 6. Efetua a cobrança via Gateway de Pagamento Desacoplado
            $paymentRequest = new PaymentRequestDTO(
                amount: (float) ($totalCents / 100),
                currency: 'BRL',
                paymentMethod: $paymentMethod,
                customerName: $custName,
                customerEmail: $custEmail,
                customerCpf: $custCpf,
                orderId: (string) $order->id
            );

            $paymentResult = $this->gatewayManager->driver($gatewayName)->charge($paymentRequest);

            if (! $paymentResult->success) {
                throw new \Exception($paymentResult->errorMessage ?? 'Falha no processamento financeiro.');
            }

            // Registra o ID da transação do gateway no código de rastreio para conciliação de webhooks
            $deliveryOrder->update(['tracking_code' => $paymentResult->transactionId]);

            // Retorna os dados do checkout de sucesso
            return [
                'success' => true,
                'order_uuid' => $order->uuid,
                'payment_data' => [
                    'transaction_id' => $paymentResult->transactionId,
                    'status' => $paymentResult->status,
                    'qr_code_url' => $paymentResult->qrCodeUrl,
                    'qr_code_base64' => $paymentResult->qrCodeBase64,
                ],
            ];
        });

        return response()->json($result);
    }

    /**
     * Endpoint para tratamento de retorno financeiro e webhooks assíncronos.
     */
    public function webhook(Request $request, string $gateway)
    {
        $payload = $request->all();

        // Dispara o processamento assíncrono e desacoplado do Webhook
        app(ProcessWebhookAction::class)->execute($gateway, $payload);

        return response()->json(['success' => true]);
    }

    /**
     * Endpoint para download ou exibição do QR Code SVG da mesa.
     */
    public function qrcode(string $publicUuid)
    {
        /** @var Table $table */
        $table = Table::where('public_uuid', $publicUuid)->firstOrFail();
        $url = route('public.menu.table', ['slug' => $table->slug]);

        $svg = $this->qrCodeService->generate($url);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qrcode-mesa-'.$table->code.'.svg"',
            'Cache-Control' => 'max-age=86400, public',
        ]);
    }
}
