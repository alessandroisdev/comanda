<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DTOs\User\CreateUserDTO;
use App\Models\User;
use App\Services\UserService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class CreateUserAction
{
    public function __construct(
        private readonly UserService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a criação de um usuário sob controle de transação e auditoria.
     */
    public function execute(CreateUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->service->create($dto);

            $this->auditService->log(
                action: 'user.create',
                before: null,
                after: $user->makeHidden('password')->toArray(), // Nunca auditar o hash da senha
                context: [
                    'user_uuid' => $user->uuid,
                    'email' => $user->email
                ]
            );

            return $user;
        });
    }
}
