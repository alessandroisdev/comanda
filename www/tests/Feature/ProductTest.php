<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\DeleteProductAction;
use App\Actions\Product\UpdateProductAction;
use App\DTOs\Product\CreateProductDTO;
use App\DTOs\Product\UpdateProductDTO;
use App\Models\Category;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private CreateProductAction $createAction;

    private UpdateProductAction $updateAction;

    private DeleteProductAction $deleteAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAction = app(CreateProductAction::class);
        $this->updateAction = app(UpdateProductAction::class);
        $this->deleteAction = app(DeleteProductAction::class);
    }

    #[Test]
    public function it_can_create_a_product_with_action_and_dto()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);

        $dto = CreateProductDTO::fromArray([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'HAMB-01',
            'barcode' => '7891234567890',
            'name' => 'Hambúrguer Gourmet',
            'description' => 'Blend de 180g de carne, queijo cheddar e molho especial',
            'price_cents' => 3890, // R$ 38,90
            'cost_cents' => 1250,  // R$ 12,50
            'status' => 'active',
            'image' => 'hamburguer.jpg',
            'preparation_time' => 15,
        ]);

        $product = $this->createAction->execute($dto);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertDatabaseHas('products', [
            'name' => 'Hambúrguer Gourmet',
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'HAMB-01',
            'price_cents' => 3890,
            'cost_cents' => 1250,
        ]);
        $this->assertNotNull($product->uuid);
    }

    #[Test]
    public function it_calculates_and_formats_prices_correctly()
    {
        $product = Product::factory()->create([
            'price_cents' => 4590, // R$ 45,90
            'cost_cents' => 1500,  // R$ 15,00
        ]);

        $this->assertEquals('R$ 45,90', $product->formatted_price);
        $this->assertEquals('R$ 15,00', $product->formatted_cost);
    }

    #[Test]
    public function it_records_an_audit_log_on_product_creation()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);

        $dto = CreateProductDTO::fromArray([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'name' => 'Refrigerante Lata',
            'price_cents' => 650,
        ]);

        $product = $this->createAction->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'product.create',
            'context' => json_encode([
                'product_uuid' => $product->uuid,
                'company_id' => $company->id,
                'category_id' => $category->id,
            ]),
        ]);
    }

    #[Test]
    public function it_can_update_a_product_with_action()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'name' => 'Old Name',
        ]);

        $dto = UpdateProductDTO::fromArray([
            'category_id' => $category->id,
            'name' => 'Updated Name',
            'price_cents' => 4200,
            'status' => 'active',
        ]);

        $updated = $this->updateAction->execute($product, $dto);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals(4200, $updated->price_cents);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function it_records_an_audit_log_on_product_update()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create([
            'company_id' => $company->id,
            'category_id' => $category->id,
        ]);

        $dto = UpdateProductDTO::fromArray([
            'category_id' => $category->id,
            'name' => 'Novo Nome',
            'price_cents' => $product->price_cents,
        ]);

        $this->updateAction->execute($product, $dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'product.update',
        ]);
    }

    #[Test]
    public function it_can_soft_delete_a_product_with_action()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create([
            'company_id' => $company->id,
            'category_id' => $category->id,
        ]);

        $this->deleteAction->execute($product);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'product.delete',
        ]);
    }

    #[Test]
    public function panel_admin_users_can_view_any_products_policy()
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Product::class));
    }

    #[Test]
    public function employee_with_permission_can_view_own_company_products_policy()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create([
            'company_id' => $company->id,
            'category_id' => $category->id,
        ]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        // Simular permissão RBAC
        $role = Role::create(['name' => 'manager']);
        $permission = Permission::create(['slug' => 'products.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee)->allows('view', $product));
    }

    #[Test]
    public function employee_cannot_view_other_company_products_policy()
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company2->id]);
        $product = Product::factory()->create([
            'company_id' => $company2->id,
            'category_id' => $category->id,
        ]);
        $employee = Employee::factory()->create(['company_id' => $company1->id]);

        // Simular permissão RBAC
        $role = Role::create(['name' => 'manager']);
        $permission = Permission::create(['slug' => 'products.view']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertFalse(Gate::forUser($employee)->allows('view', $product));
    }

    #[Test]
    public function employee_with_permission_can_delete_own_company_product_policy()
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create([
            'company_id' => $company->id,
            'category_id' => $category->id,
        ]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        // Simular permissão RBAC
        $role = Role::create(['name' => 'manager']);
        $permission = Permission::create(['slug' => 'products.delete']);
        $role->permissions()->attach($permission);
        $employee->roles()->attach($role);

        $this->assertTrue(Gate::forUser($employee)->allows('delete', $product));
    }
}
