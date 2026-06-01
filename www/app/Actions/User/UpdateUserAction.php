<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DTOs\User\UpdateUserDTO;
use App\Models\User;
use App\Services\UserService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class UpdateUserAction
{
    public function __construct(
        private readonly UserService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a atualização de um usuário.
     */
    public function execute(User $user, UpdateUserDTO $dto): User
    {
        return DB::transaction(function () use ($user, $dto) {
            $before = $user->makeHidden('password')->toArray();

            $updatedUser = $this->service->update($user, $dto);

            $this->auditService->log(
                action: 'user.update',
                before: $before,
                after: $updatedUser->makeHidden('password')->toArray(),
                context: [
                    'user_uuid' => $updatedUser->uuid,
                    'email' => $updatedUser->email,
                    'password_reset' => ! empty($dto->password) // Flag de alteração de senha
                ]
            );

            return $updatedUser;
        });
    }
}
