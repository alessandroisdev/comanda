<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class RbacStructureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_all_rbac_tables_migrated_correctly()
    {
        $this->assertTrue(Schema::hasTable('roles'));
        $this->assertTrue(Schema::hasTable('permissions'));
        $this->assertTrue(Schema::hasTable('role_permission'));
        $this->assertTrue(Schema::hasTable('employee_role'));
    }

    /** @test */
    public function it_can_associate_roles_and_permissions()
    {
        // Inserir Role e Permission mockados
        $roleId = DB::table('roles')->insertGetId([
            'uuid' => 'f3b392a8-129b-43d9-a9a3-a5c7c2512f45',
            'name' => 'manager',
            'description' => 'Manager Role'
        ]);

        $permissionId = DB::table('permissions')->insertGetId([
            'uuid' => 'a3b392a8-129b-43d9-a9a3-a5c7c2512f11',
            'slug' => 'orders.status.update',
            'description' => 'Update order status'
        ]);

        // Associar na tabela intermediária
        DB::table('role_permission')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);

        $this->assertDatabaseHas('role_permission', [
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }
}
