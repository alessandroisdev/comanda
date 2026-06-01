<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\DTOs\Customer\CreateCustomerDTO;
use App\Models\Customer;
use App\Services\Audit\AuditService;
use App\Services\CustomerService;
use Illuminate\Support\Facades\DB;

class CreateCustomerAction
{
    public function __construct(
        private readonly CustomerService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a criação do cliente sob transação e auditoria.
     */
    public function execute(CreateCustomerDTO $dto): Customer
    {
        return DB::transaction(function () use ($dto) {
            $customer = $this->service->create($dto);

            $this->auditService->log(
                action: 'customer.create',
                before: null,
                after: $customer->toArray(),
                context: [
                    'customer_uuid' => $customer->uuid,
                    'company_id' => $customer->company_id,
                ]
            );

            return $customer;
        });
    }
}
