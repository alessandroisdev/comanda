<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use App\Enums\CategoryStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controlado pela Policy via Gate::authorize
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', new Enum(CategoryStatusEnum::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
