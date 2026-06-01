<?php

declare(strict_types=1);

namespace App\Actions\CashierShift;

use App\Enums\CashierShiftStatusEnum;
use App\Models\CashierShift;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CloseCashierShiftAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(CashierShift $shift, int $employeeId, int $closingAmountCents): CashierShift
    {
        return DB::transaction(function () use ($shift, $employeeId, $closingAmountCents) {
            $shift->update([
                'status' => CashierShiftStatusEnum::CLOSED,
                'closed_by' => $employeeId,
                'closed_at' => Carbon::now(),
                'closing_amount_cents' => $closingAmountCents,
            ]);

            // Registrar log de auditoria
            $this->auditService->log('cashier.close_shift', [
                'shift_uuid' => $shift->uuid,
                'company_id' => $shift->company_id,
                'unit_id' => $shift->unit_id,
                'closed_by' => $employeeId,
                'closing_amount_cents' => $closingAmountCents,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.cashier', 'cashier.closed', [
                'uuid' => $shift->uuid,
                'closed_at' => $shift->closed_at->toDateTimeString(),
                'status' => $shift->status->value,
            ]);

            return $shift;
        });
    }
}
