<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\DTOs\Product\UpdateProductDTO;
use App\Models\Product;
use App\Services\Audit\AuditService;
use App\Services\ProductService;
use Illuminate\Support\Facades\DB;

class UpdateProductAction
{
    public function __construct(
        private readonly ProductService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a atualização do produto sob transação e auditoria.
     */
    public function execute(Product $product, UpdateProductDTO $dto): Product
    {
        return DB::transaction(function () use ($product, $dto) {
            $before = $product->toArray();

            $updatedProduct = $this->service->update($product, $dto);

            $this->auditService->log(
                action: 'product.update',
                before: $before,
                after: $updatedProduct->toArray(),
                context: [
                    'product_uuid' => $updatedProduct->uuid,
                    'company_id' => $updatedProduct->company_id,
                    'category_id' => $updatedProduct->category_id,
                ]
            );

            return $updatedProduct;
        });
    }
}
