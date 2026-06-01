<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Busca um usuário pelo seu UUID público.
     *
     * @throws ModelNotFoundException
     */
    public function findByUuid(string $uuid): User
    {
        return User::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Cria e persiste um novo usuário.
     */
    public function create(CreateUserDTO $dto): User
    {
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password), // Hashing criptográfico
            'status' => $dto->status,
        ]);
    }

    /**
     * Atualiza os dados de um usuário existente, incluindo suporte opcional a reset de senha.
     */
    public function update(User $user, UpdateUserDTO $dto): User
    {
        $data = [
            'name' => $dto->name,
            'email' => $dto->email,
            'status' => $dto->status ?? $user->status,
        ];

        if ($dto->password) {
            $data['password'] = Hash::make($dto->password);
        }

        $user->update($data);

        return $user;
    }

    /**
     * Remove um usuário via soft delete.
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
