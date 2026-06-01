<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use App\Services\UserService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class DeleteUserAction
{
    public function __construct(
        private readonly UserService $service,
        private readonly AuditService $auditService
    ) {}

    /**
     * Executa a exclusão de um usuário.
     */
    public function execute(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $before = $user->makeHidden('password')->toArray();
            $uuid = $user->uuid;
            $email = $user->email;

            $result = $this->service->delete($user);

            $this->auditService->log(
                action: 'user.delete',
                before: $before,
                after: null,
                context: [
                    'user_uuid' => $uuid,
                    'email' => $email
                ]
            );

            return $result;
        });
    }
}
