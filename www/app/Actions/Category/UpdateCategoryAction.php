<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\DTOs\Category\UpdateCategoryDTO;
use App\Models\Category;
use App\Services\Audit\AuditService;
use App\Services\CategoryService;
use Illuminate\Support\Facades\DB;

class UpdateCategoryAction
{
    public function __construct(
        private readonly CategoryService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a atualização da categoria sob transação e auditoria.
     */
    public function execute(Category $category, UpdateCategoryDTO $dto): Category
    {
        return DB::transaction(function () use ($category, $dto) {
            $before = $category->toArray();

            $updatedCategory = $this->service->update($category, $dto);

            $this->auditService->log(
                action: 'category.update',
                before: $before,
                after: $updatedCategory->toArray(),
                context: [
                    'category_uuid' => $updatedCategory->uuid,
                    'company_id' => $updatedCategory->company_id,
                ]
            );

            return $updatedCategory;
        });
    }
}
