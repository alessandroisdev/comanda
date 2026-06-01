<?php

declare(strict_types=1);

namespace App\Actions\PrintJob;

use App\Enums\PrintJobStatusEnum;
use App\Models\PrintJob;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class EnqueuePrintJobAction
{
    public function __construct(private readonly AuditService $auditService) {}

    public function execute(array $data): PrintJob
    {
        return DB::transaction(function () use ($data) {
            $job = PrintJob::create([
                'company_id' => $data['company_id'],
                'unit_id' => $data['unit_id'],
                'type' => $data['type'],
                'payload' => $data['payload'],
                'status' => PrintJobStatusEnum::PENDING,
                'attempts' => 0,
            ]);

            // Registrar log de auditoria
            $this->auditService->log('print.enqueue', [
                'print_job_uuid' => $job->uuid,
                'company_id' => $job->company_id,
                'unit_id' => $job->unit_id,
                'type' => $job->type,
            ]);

            return $job;
        });
    }
}
