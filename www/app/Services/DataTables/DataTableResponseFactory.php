<?php

declare(strict_types=1);

namespace App\Services\DataTables;

use Illuminate\Http\JsonResponse;

class DataTableResponseFactory
{
    /**
     * Cria uma resposta JSON em conformidade com as regras do DataTables.
     */
    public static function create(array $data): JsonResponse
    {
        return response()->json([
            'draw' => $data['draw'] ?? 1,
            'recordsTotal' => $data['recordsTotal'] ?? 0,
            'recordsFiltered' => $data['recordsFiltered'] ?? 0,
            'data' => $data['data'] ?? [],
        ]);
    }
}
