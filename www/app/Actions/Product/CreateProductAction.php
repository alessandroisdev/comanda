<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\DTOs\Product\CreateProductDTO;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    public function __construct(
        private readonly ProductService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a criação do produto sob transação e auditoria.
     */
    public function execute(CreateProductDTO $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $product = $this->service->create($dto);

            $this->auditService->log(
                action: 'product.create',
                before: null,
                after: $product->toArray(),
                context: [
                    'product_uuid' => $product->uuid,
                    'company_id' => $product->company_id,
                    'category_id' => $product->category_id,
                ]
            );

            return $product;
        });
    }
}
