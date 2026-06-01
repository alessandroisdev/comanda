<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class DeleteCustomerAction
{
    public function __construct(
        private readonly CustomerService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a exclusão lógica do cliente sob transação e auditoria.
     */
    public function execute(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            $before = $customer->toArray();

            $this->service->delete($customer);

            $this->auditService->log(
                action: 'customer.delete',
                before: $before,
                after: null,
                context: [
                    'customer_uuid' => $customer->uuid,
                    'company_id' => $customer->company_id,
                ]
            );
        });
    }
}
