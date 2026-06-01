<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Category\CreateCategoryAction;
use App\Actions\Category\UpdateCategoryAction;
use App\Actions\Category\DeleteCategoryAction;
use App\DTOs\Category\CreateCategoryDTO;
use App\DTOs\Category\UpdateCategoryDTO;
use App\Models\Company;
use App\Models\Category;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private CreateCategoryAction $createAction;
    private UpdateCategoryAction $updateAction;
    private DeleteCategoryAction $deleteAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAction = app(CreateCategoryAction::class);
        $this->updateAction = app(UpdateCategoryAction::class);
        $this->deleteAction = app(DeleteCategoryAction::class);
    }

    #[Test]
    public function it_can_create_a_category_with_action_and_dto()
    {
        $company = Company::factory()->create();

        $dto = CreateCategoryDTO::fromArray([
            'company_id' => $company->id,
            'name' => 'Bebidas Geladas',
            'description' => 'Sucos, refrigerantes e águas',
            'status' => 'active',
            'sort_order' => 5
        ]);

        $category = $this->createAction->execute($dto);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertDatabaseHas('categories', [
            'name' => 'Bebidas Geladas',
            'company_id' => $company->id,
            'sort_order' => 5,
        ]);
        $this->assertNotNull($category->uuid);
    }

    #[Test]
    public function it_records_an_audit_log_on_category_creation()
    {
        $company = Company::factory()->create();

        $dto = CreateCategoryDTO::fromArray([
            'company_id' => $company->id,
            'name' => 'Lanches',
        ]);

        $category = $this->createAction->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'category.create',
            'context' => json_encode(['category_uuid' => $category->uuid, 'company_id' => $company->id]),
        ]);
    }

    #[Test]
    public function it_can_update_a_category_with_action()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create([
            'company_id' => $company->id,
            'name' => 'Old Name'
        ]);

        $dto = UpdateCategoryDTO::fromArray([
            'name' => 'Updated Name',
            'status' => 'active',
            'sort_order' => 10
        ]);

        $updated = $this->updateAction->execute($category, $dto);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals(10, $updated->sort_order);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function it_records_an_audit_log_on_category_update()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);

        $dto = UpdateCategoryDTO::fromArray([
            'name' => 'Novo Nome',
        ]);

        $this->updateAction->execute($category, $dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'category.update',
        ]);
    }

    #[Test]
    public function it_can_soft_delete_a_category_with_action()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);

        $this->deleteAction->execute($category);

        $this->assertSoftDeleted('categories', [
            'id' => $category->id
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'category.delete',
        ]);
    }

    #[Test]
    public function panel_admin_users_can_view_any_categories_policy()
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Category::class));
    }

    #[Test]
    public function employee_with_permission_can_view_own_company_categories_policy()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'categories.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee)->allows('view', $category));
    }

    #[Test]
    public function employee_cannot_view_other_company_categories_policy()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company2->id]);
        $employee = Employee::factory()->create(['company_id' => $company1->id]);

        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'categories.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertFalse(Gate::forUser($employee)->allows('view', $category));
    }

    #[Test]
    public function employee_without_permission_cannot_view_own_categories_policy()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $this->assertFalse(Gate::forUser($employee)->allows('view', $category));
    }

    #[Test]
    public function employee_with_permission_can_delete_own_company_category_policy()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        // Simular permissão RBAC
        $role = \App\Models\Role::create(['name' => 'manager']);
        $permission = \App\Models\Permission::create(['slug' => 'categories.delete']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee)->allows('delete', $category));
    }
}
