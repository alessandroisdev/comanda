<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
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
        $userUuid = $this->route('user');
        $userId = null;

        if ($userUuid) {
            $userId = User::where('uuid', $userUuid)->value('id');
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'email', 
                'max:150', 
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
            'status' => ['nullable', new Enum(UserStatusEnum::class)],
        ];
    }
}
