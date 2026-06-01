<?php

declare(strict_types=1);

namespace App\Actions\CashierShift;

use App\Enums\CashierShiftStatusEnum;
use App\Models\CashierShift;
use App\Services\Audit\AuditService;
use App\Services\SSE\SseQueueService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OpenCashierShiftAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(array $data): CashierShift
    {
        return DB::transaction(function () use ($data) {
            $shift = CashierShift::create([
                'company_id' => $data['company_id'],
                'unit_id' => $data['unit_id'],
                'opened_by' => $data['opened_by'],
                'opened_at' => Carbon::now(),
                'opening_amount_cents' => (int) $data['opening_amount_cents'],
                'status' => CashierShiftStatusEnum::OPEN,
            ]);

            // Registrar log de auditoria
            $this->auditService->log('cashier.open_shift', [
                'shift_uuid' => $shift->uuid,
                'company_id' => $shift->company_id,
                'unit_id' => $shift->unit_id,
                'opened_by' => $shift->opened_by,
                'opening_amount_cents' => $shift->opening_amount_cents,
            ]);

            // Publicar evento SSE reativo
            SseQueueService::publish('admin.cashier', 'cashier.opened', [
                'uuid' => $shift->uuid,
                'opened_at' => $shift->opened_at->toDateTimeString(),
                'status' => $shift->status->value,
            ]);

            return $shift;
        });
    }
}
