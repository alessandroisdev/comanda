<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Enums\ProductStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controlado pela Policy via Gate::authorize
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('company_id', $this->input('company_id')),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products')->where(fn ($query) => $query->where('company_id', $this->input('company_id'))),
            ],
            'barcode' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'price_cents' => ['required', 'integer', 'min:1'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'cost_cents' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', new Enum(ProductStatusEnum::class)],
            'image' => ['nullable', 'string', 'max:255'],
            'preparation_time' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('price')) {
            $price = $this->input('price');
            $price = str_replace(',', '.', (string) $price);
            $this->merge([
                'price_cents' => (int) round(((float) $price) * 100),
            ]);
        }
        if ($this->has('cost')) {
            $cost = $this->input('cost');
            if ($cost !== null && $cost !== '') {
                $cost = str_replace(',', '.', (string) $cost);
                $this->merge([
                    'cost_cents' => (int) round(((float) $cost) * 100),
                ]);
            } else {
                $this->merge([
                    'cost_cents' => null,
                ]);
            }
        }
    }
}
