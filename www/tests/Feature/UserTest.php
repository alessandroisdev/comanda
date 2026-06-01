<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\User\CreateUserAction;
use App\Actions\User\UpdateUserAction;
use App\Actions\User\DeleteUserAction;
use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private CreateUserAction $createAction;
    private UpdateUserAction $updateAction;
    private DeleteUserAction $deleteAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAction = app(CreateUserAction::class);
        $this->updateAction = app(UpdateUserAction::class);
        $this->deleteAction = app(DeleteUserAction::class);
    }

    #[Test]
    public function it_can_create_a_user_with_action_and_dto()
    {
        $dto = CreateUserDTO::fromArray([
            'name' => 'Admin Teste',
            'email' => 'admin.teste@comanda.com',
            'password' => 'password123',
            'status' => 'active'
        ]);

        $user = $this->createAction->execute($dto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'name' => 'Admin Teste',
            'email' => 'admin.teste@comanda.com',
            'status' => 'active',
        ]);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    #[Test]
    public function it_records_an_audit_log_on_user_creation()
    {
        $dto = CreateUserDTO::fromArray([
            'name' => 'Admin Audit',
            'email' => 'admin.audit@comanda.com',
            'password' => 'password123',
        ]);

        $user = $this->createAction->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.create',
            'context' => json_encode(['user_uuid' => $user->uuid, 'email' => $user->email]),
        ]);
    }

    #[Test]
    public function it_can_update_a_user_details_and_optionally_password()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'password' => Hash::make('oldpassword')
        ]);

        $dto = UpdateUserDTO::fromArray([
            'name' => 'New Name',
            'email' => $user->email,
            'password' => 'newpassword123',
            'status' => 'active'
        ]);

        $updated = $this->updateAction->execute($user, $dto);

        $this->assertEquals('New Name', $updated->name);
        $this->assertTrue(Hash::check('newpassword123', $updated->password));
    }

    #[Test]
    public function it_records_an_audit_log_on_user_update()
    {
        $user = User::factory()->create();

        $dto = UpdateUserDTO::fromArray([
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $this->updateAction->execute($user, $dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.update',
        ]);
    }

    #[Test]
    public function it_can_soft_delete_a_user_with_action()
    {
        $user = User::factory()->create();

        $this->deleteAction->execute($user);

        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.delete',
        ]);
    }

    #[Test]
    public function panel_admin_users_can_view_any_users_policy()
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', User::class));
    }

    #[Test]
    public function employees_cannot_view_users_policy()
    {
        $employee = Employee::factory()->create();

        $this->assertFalse(Gate::forUser($employee)->allows('viewAny', User::class));
    }

    #[Test]
    public function panel_admin_users_cannot_delete_themselves_policy()
    {
        $user = User::factory()->create();

        $this->assertFalse(Gate::forUser($user)->allows('delete', $user));
    }

    #[Test]
    public function panel_admin_users_can_delete_other_users_policy()
    {
        $admin1 = User::factory()->create();
        $admin2 = User::factory()->create();

        $this->assertTrue(Gate::forUser($admin1)->allows('delete', $admin2));
    }

    #[Test]
    public function panel_admin_users_can_view_specific_user_policy()
    {
        $admin1 = User::factory()->create();
        $admin2 = User::factory()->create();

        $this->assertTrue(Gate::forUser($admin1)->allows('view', $admin2));
    }
}
