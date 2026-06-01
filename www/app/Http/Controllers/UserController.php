<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\CreateUserAction;
use App\Actions\User\UpdateUserAction;
use App\Actions\User\DeleteUserAction;
use App\DataTables\UsersDataTable;
use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use App\Services\DataTables\DataTableQueryService;
use App\Services\DataTables\DataTableResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $service,
        private readonly DataTableQueryService $dataTableService
    ) {}

    /**
     * Exibe a listagem dos usuários administrativos.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        return view('admin.users.index');
    }

    /**
     * Endpoint API para o DataTables de Usuários.
     */
    public function datatable(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $provider = new UsersDataTable();
        $result = $this->dataTableService->process($request, $provider);

        return DataTableResponseFactory::create($result);
    }

    /**
     * Exibe o formulário de criação de usuário.
     */
    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.create');
    }

    /**
     * Armazena um novo usuário no banco.
     */
    public function store(CreateUserRequest $request, CreateUserAction $action)
    {
        Gate::authorize('create', User::class);

        $dto = CreateUserDTO::fromArray($request->validated());
        $user = $action->execute($dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('users.messages.create_success'),
                'user_uuid' => $user->uuid
            ], 201);
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('users.messages.create_success'));
    }

    /**
     * Exibe detalhes de um usuário.
     */
    public function show(string $uuid): View
    {
        $user = $this->service->findByUuid($uuid);
        Gate::authorize('view', $user);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Exibe o formulário de edição de um usuário.
     */
    public function edit(string $uuid): View
    {
        $user = $this->service->findByUuid($uuid);
        Gate::authorize('update', $user);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Atualiza os dados de um usuário no banco.
     */
    public function update(UpdateUserRequest $request, string $uuid, UpdateUserAction $action)
    {
        $user = $this->service->findByUuid($uuid);
        Gate::authorize('update', $user);

        $dto = UpdateUserDTO::fromArray($request->validated());
        $updatedUser = $action->execute($user, $dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('users.messages.update_success'),
                'user_uuid' => $updatedUser->uuid
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('users.messages.update_success'));
    }

    /**
     * Remove um usuário (soft delete).
     */
    public function destroy(string $uuid, DeleteUserAction $action): JsonResponse
    {
        $user = $this->service->findByUuid($uuid);
        Gate::authorize('delete', $user);

        $action->execute($user);

        return response()->json([
            'success' => true,
            'message' => __('users.messages.delete_success')
        ]);
    }
}
