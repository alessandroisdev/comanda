<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Category\CreateCategoryDTO;
use App\DTOs\Category\UpdateCategoryDTO;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryService
{
    /**
     * Busca uma categoria pelo seu UUID público.
     *
     * @throws ModelNotFoundException
     */
    public function findByUuid(string $uuid): Category
    {
        return Category::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Cria e persiste uma nova categoria a partir do DTO correspondente.
     */
    public function create(CreateCategoryDTO $dto): Category
    {
        return Category::create([
            'company_id' => $dto->company_id,
            'name' => $dto->name,
            'description' => $dto->description,
            'status' => $dto->status,
            'sort_order' => $dto->sort_order,
        ]);
    }

    /**
     * Atualiza os dados de uma categoria a partir do DTO correspondente.
     */
    public function update(Category $category, UpdateCategoryDTO $dto): Category
    {
        $category->update(array_filter([
            'name' => $dto->name,
            'description' => $dto->description,
            'status' => $dto->status,
            'sort_order' => $dto->sort_order,
        ], fn ($value) => $value !== null));

        return $category;
    }

    /**
     * Exclui logicamente a categoria.
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
