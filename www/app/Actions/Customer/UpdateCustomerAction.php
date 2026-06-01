<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\DTOs\Customer\UpdateCustomerDTO;
use App\Models\Customer;
use App\Services\Audit\AuditService;
use App\Services\CustomerService;
use Illuminate\Support\Facades\DB;

class UpdateCustomerAction
{
    public function __construct(
        private readonly CustomerService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a atualização do cliente sob transação e auditoria.
     */
    public function execute(Customer $customer, UpdateCustomerDTO $dto): Customer
    {
        return DB::transaction(function () use ($customer, $dto) {
            $before = $customer->toArray();

            $updatedCustomer = $this->service->update($customer, $dto);

            $this->auditService->log(
                action: 'customer.update',
                before: $before,
                after: $updatedCustomer->toArray(),
                context: [
                    'customer_uuid' => $updatedCustomer->uuid,
                    'company_id' => $updatedCustomer->company_id,
                ]
            );

            return $updatedCustomer;
        });
    }
}
