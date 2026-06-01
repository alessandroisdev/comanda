<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\DTOs\Category\CreateCategoryDTO;
use App\Models\Category;
use App\Services\Audit\AuditService;
use App\Services\CategoryService;
use Illuminate\Support\Facades\DB;

class CreateCategoryAction
{
    public function __construct(
        private readonly CategoryService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a criação da categoria sob transação e auditoria.
     */
    public function execute(CreateCategoryDTO $dto): Category
    {
        return DB::transaction(function () use ($dto) {
            $category = $this->service->create($dto);

            $this->auditService->log(
                action: 'category.create',
                before: null,
                after: $category->toArray(),
                context: [
                    'category_uuid' => $category->uuid,
                    'company_id' => $category->company_id,
                ]
            );

            return $category;
        });
    }
}
