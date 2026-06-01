<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Models\Category;
use App\Services\CategoryService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class DeleteCategoryAction
{
    public function __construct(
        private readonly CategoryService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a exclusão lógica da categoria sob transação e auditoria.
     */
    public function execute(Category $category): void
    {
        DB::transaction(function () use ($category) {
            $before = $category->toArray();

            $this->service->delete($category);

            $this->auditService->log(
                action: 'category.delete',
                before: $before,
                after: null,
                context: [
                    'category_uuid' => $category->uuid,
                    'company_id' => $category->company_id,
                ]
            );
        });
    }
}
