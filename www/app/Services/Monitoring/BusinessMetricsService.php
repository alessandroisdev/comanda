<?php

declare(strict_types=1);

namespace App\Services\Monitoring;

use App\Models\Order;
use App\Models\Table;
use App\Models\DeliveryOrder;
use App\Models\KitchenTicket;
use App\Enums\OrderStatusEnum;
use App\Enums\TableStatusEnum;
use App\Enums\KitchenTicketStatusEnum;
use Carbon\Carbon;

class BusinessMetricsService
{
    /**
     * Coleta métricas de negócio do estabelecimento (respeitando tenant se fornecido).
     */
    public function getBusinessMetrics(?int $companyId = null, ?int $unitId = null): array
    {
        $orderQuery = Order::query()->where('status', '!=', OrderStatusEnum::CANCELLED);
        $tableQuery = Table::query();
        $deliveryQuery = DeliveryOrder::query()->whereIn('status', ['assigned', 'dispatched']);
        $kitchenQuery = KitchenTicket::query()->whereIn('status', [
            KitchenTicketStatusEnum::PENDING,
            KitchenTicketStatusEnum::PREPARING,
        ]);

        if ($companyId !== null) {
            $orderQuery->where('company_id', $companyId);
            $tableQuery->where('company_id', $companyId);
            $deliveryQuery->where('company_id', $companyId);
            $kitchenQuery->whereHas('order', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        if ($unitId !== null) {
            $orderQuery->where('unit_id', $unitId);
            $tableQuery->where('unit_id', $unitId);
            $deliveryQuery->where('unit_id', $unitId);
            $kitchenQuery->whereHas('order', function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        }

        // 1. Pedidos criados na última hora
        $recentOrdersCount = (clone $orderQuery)
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();

        // 2. Ticket Médio (em centavos)
        $avgTicketCents = (int) round((clone $orderQuery)->avg('total_cents') ?? 0);

        // 3. Vendas totais de hoje (em centavos)
        $salesTodayCents = (clone $orderQuery)
            ->where('created_at', '>=', Carbon::now()->startOfDay())
            ->sum('total_cents');

        // 4. Mesas ocupadas
        $occupiedTablesCount = (clone $tableQuery)
            ->where('status', TableStatusEnum::OCCUPIED)
            ->count();

        // 5. Entregas em andamento
        $pendingDeliveriesCount = $deliveryQuery->count();

        // 6. Pedidos em produção na cozinha
        $productionTicketsCount = $kitchenQuery->count();

        return [
            'orders_last_hour' => $recentOrdersCount,
            'average_ticket_cents' => $avgTicketCents,
            'sales_today_cents' => $salesTodayCents,
            'occupied_tables' => $occupiedTablesCount,
            'deliveries_in_progress' => $pendingDeliveriesCount,
            'orders_in_production' => $productionTicketsCount,
        ];
    }
}
