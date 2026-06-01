<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Services\ProductService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class DeleteProductAction
{
    public function __construct(
        private readonly ProductService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a exclusão lógica do produto sob transação e auditoria.
     */
    public function execute(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $before = $product->toArray();

            $this->service->delete($product);

            $this->auditService->log(
                action: 'product.delete',
                before: $before,
                after: null,
                context: [
                    'product_uuid' => $product->uuid,
                    'company_id' => $product->company_id,
                ]
            );
        });
    }
}
