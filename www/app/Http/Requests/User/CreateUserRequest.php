<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\UserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine se o usuário está autorizado a fazer este request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obter as regras de validação aplicáveis ao request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
            'status' => ['nullable', new Enum(UserStatusEnum::class)],
        ];
    }
}
