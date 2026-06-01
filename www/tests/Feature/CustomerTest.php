<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Customer\CreateCustomerAction;
use App\Actions\Customer\UpdateCustomerAction;
use App\Actions\Customer\DeleteCustomerAction;
use App\DTOs\Customer\CreateCustomerDTO;
use App\DTOs\Customer\UpdateCustomerDTO;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private CreateCustomerAction $createAction;
    private UpdateCustomerAction $updateAction;
    private DeleteCustomerAction $deleteAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAction = app(CreateCustomerAction::class);
        $this->updateAction = app(UpdateCustomerAction::class);
        $this->deleteAction = app(DeleteCustomerAction::class);
    }

    #[Test]
    public function it_can_create_a_customer_with_action_and_dto()
    {
        $company = Company::factory()->create();

        $dto = CreateCustomerDTO::fromArray([
            'company_id' => $company->id,
            'name' => 'Maria Silva',
            'email' => 'maria.silva@gmail.com',
            'password' => 'pass123',
            'phone' => '11988889999',
            'document' => '222.333.444-55',
            'birth_date' => '1990-10-10',
            'marketing_opt_in' => true,
            'status' => 'active'
        ]);

        $customer = $this->createAction->execute($dto);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertDatabaseHas('customers', [
            'name' => 'Maria Silva',
            'company_id' => $company->id,
            'email' => 'maria.silva@gmail.com',
            'marketing_opt_in' => true,
        ]);
        $this->assertTrue(Hash::check('pass123', $customer->password));
    }

    #[Test]
    public function it_records_an_audit_log_on_customer_creation()
    {
        $company = Company::factory()->create();

        $dto = CreateCustomerDTO::fromArray([
            'company_id' => $company->id,
            'name' => 'Maria Audit',
            'email' => 'maria.audit@gmail.com',
            'password' => 'pass123',
        ]);

        $customer = $this->createAction->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'customer.create',
            'context' => json_encode(['customer_uuid' => $customer->uuid, 'company_id' => $company->id]),
        ]);
    }

    #[Test]
    public function it_can_update_a_customer_with_action()
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'name' => 'Old Name'
        ]);

        $dto = UpdateCustomerDTO::fromArray([
            'name' => 'Updated Name',
            'email' => $customer->email,
            'status' => 'active',
            'marketing_opt_in' => false
        ]);

        $updated = $this->updateAction->execute($customer, $dto);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertFalse($updated->marketing_opt_in);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function it_records_an_audit_log_on_customer_update()
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        $dto = UpdateCustomerDTO::fromArray([
            'name' => 'Novo Nome',
            'email' => $customer->email,
        ]);

        $this->updateAction->execute($customer, $dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'customer.update',
        ]);
    }

    #[Test]
    public function it_can_soft_delete_a_customer_with_action()
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        $this->deleteAction->execute($customer);

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'customer.delete',
        ]);
    }

    #[Test]
    public function panel_admin_users_can_view_any_customers_policy()
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Customer::class));
    }

    #[Test]
    public function employee_with_permission_can_view_own_company_customers_policy()
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'customers.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee)->allows('view', $customer));
    }

    #[Test]
    public function employee_cannot_view_other_company_customers_policy()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company2->id]);
        $employee = Employee::factory()->create(['company_id' => $company1->id]);

        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'customers.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertFalse(Gate::forUser($employee)->allows('view', $customer));
    }

    #[Test]
    public function employee_without_permission_cannot_view_own_customers_policy()
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $this->assertFalse(Gate::forUser($employee)->allows('view', $customer));
    }

    #[Test]
    public function employee_with_permission_can_delete_own_company_customer_policy()
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'customers.delete']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee)->allows('delete', $customer));
    }
}
