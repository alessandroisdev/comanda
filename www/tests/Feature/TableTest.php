<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Table\ChangeTableStatusAction;
use App\Actions\Table\CreateTableAction;
use App\Actions\Table\DeleteTableAction;
use App\Actions\Table\UpdateTableAction;
use App\DTOs\Table\CreateTableDTO;
use App\DTOs\Table\UpdateTableDTO;
use App\Enums\TableStatusEnum;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TableTest extends TestCase
{
    use RefreshDatabase;

    private CreateTableAction $createAction;

    private UpdateTableAction $updateAction;

    private DeleteTableAction $deleteAction;

    private ChangeTableStatusAction $changeStatusAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAction = app(CreateTableAction::class);
        $this->updateAction = app(UpdateTableAction::class);
        $this->deleteAction = app(DeleteTableAction::class);
        $this->changeStatusAction = app(ChangeTableStatusAction::class);
    }

    #[Test]
    public function it_can_create_a_table_with_action_and_dto()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $dto = CreateTableDTO::fromArray([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'code' => 'M-01',
            'name' => 'Mesa 01',
            'capacity' => 4,
            'sector' => 'Salão Principal',
            'status' => 'available',
            'sort_order' => 1,
        ]);

        $table = $this->createAction->execute($dto);

        $this->assertInstanceOf(Table::class, $table);
        $this->assertDatabaseHas('tables', [
            'code' => 'M-01',
            'name' => 'Mesa 01',
            'company_id' => $company->id,
            'capacity' => 4,
            'status' => 'available',
        ]);
        $this->assertNotNull($table->uuid);
    }

    #[Test]
    public function it_records_an_audit_log_on_table_creation()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $dto = CreateTableDTO::fromArray([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'code' => 'M-02',
            'name' => 'Mesa 02',
            'capacity' => 6,
            'sector' => 'Varanda',
            'status' => 'available',
        ]);

        $table = $this->createAction->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'table.create',
        ]);
    }

    #[Test]
    public function it_can_update_a_table_with_action()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $table = Table::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'name' => 'Mesa Antiga',
        ]);

        $dto = UpdateTableDTO::fromArray([
            'code' => 'M-X',
            'name' => 'Mesa Atualizada',
            'capacity' => 8,
            'sector' => 'VIP',
            'sort_order' => 50,
        ]);

        $updated = $this->updateAction->execute($table, $dto);

        $this->assertEquals('Mesa Atualizada', $updated->name);
        $this->assertEquals(8, $updated->capacity);
        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'name' => 'Mesa Atualizada',
        ]);
    }

    #[Test]
    public function it_can_change_table_status()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $table = Table::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'status' => TableStatusEnum::AVAILABLE,
        ]);

        $this->changeStatusAction->execute($table, TableStatusEnum::OCCUPIED);

        $this->assertEquals(TableStatusEnum::OCCUPIED, $table->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'table.status_changed',
        ]);
    }

    #[Test]
    public function it_can_soft_delete_a_table_with_action()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $table = Table::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
        ]);

        $this->deleteAction->execute($table);

        $this->assertSoftDeleted('tables', [
            'id' => $table->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'table.delete',
        ]);
    }

    #[Test]
    public function panel_admin_users_can_view_any_tables_policy()
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Table::class));
    }

    #[Test]
    public function employee_with_permission_can_view_own_company_tables_policy()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $table = Table::factory()->create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
        ]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $role = Role::create(['name' => 'waiter']);
        $permission = Permission::create(['slug' => 'tables.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee)->allows('view', $table));
    }

    #[Test]
    public function employee_cannot_view_other_company_tables_policy()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $unit2 = CompanyUnit::factory()->create(['company_id' => $company2->id]);
        $table = Table::factory()->create([
            'company_id' => $company2->id,
            'unit_id' => $unit2->id,
        ]);
        $employee = Employee::factory()->create(['company_id' => $company1->id]);

        $role = Role::create(['name' => 'waiter']);
        $permission = Permission::create(['slug' => 'tables.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertFalse(Gate::forUser($employee)->allows('view', $table));
    }
}
