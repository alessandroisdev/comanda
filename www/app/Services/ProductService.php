<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Product\CreateProductDTO;
use App\DTOs\Product\UpdateProductDTO;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductService
{
    /**
     * Busca um produto pelo seu UUID público.
     *
     * @throws ModelNotFoundException
     */
    public function findByUuid(string $uuid): Product
    {
        return Product::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Cria e persiste um novo produto a partir do DTO correspondente.
     */
    public function create(CreateProductDTO $dto): Product
    {
        return Product::create([
            'company_id' => $dto->company_id,
            'category_id' => $dto->category_id,
            'sku' => $dto->sku,
            'barcode' => $dto->barcode,
            'name' => $dto->name,
            'description' => $dto->description,
            'price_cents' => $dto->price_cents,
            'cost_cents' => $dto->cost_cents,
            'status' => $dto->status,
            'image' => $dto->image,
            'preparation_time' => $dto->preparation_time,
        ]);
    }

    /**
     * Atualiza os dados de um produto a partir do DTO correspondente.
     */
    public function update(Product $product, UpdateProductDTO $dto): Product
    {
        $data = array_filter([
            'category_id' => $dto->category_id,
            'sku' => $dto->sku,
            'barcode' => $dto->barcode,
            'name' => $dto->name,
            'description' => $dto->description,
            'price_cents' => $dto->price_cents,
            'status' => $dto->status,
            'image' => $dto->image,
            'preparation_time' => $dto->preparation_time,
        ], fn ($value) => $value !== null);

        // Opcional cost_cents pode ser desvinculado (nulo), então tratamos explicitamente
        $data['cost_cents'] = $dto->cost_cents;

        $product->update($data);

        return $product;
    }

    /**
     * Exclui logicamente o produto do sistema.
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
