<?php

namespace Tests\Feature;

use App\Models\Module;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulesCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_module_with_valid_attributes()
    {
        $module = Module::create([
            'name' => 'Modulo Teste',
            'code' => 'modulo_teste',
            'description' => 'Descricao modulo teste',
            'price_suggested_cents' => 9900,
            'status' => 'active',
            'version_min' => '1.0.0',
        ]);

        $this->assertDatabaseHas('modules', [
            'code' => 'modulo_teste',
            'price_suggested_cents' => 9900,
        ]);
    }

    public function test_it_validates_module_code_uniqueness()
    {
        Module::create([
            'name' => 'Modulo Teste 1',
            'code' => 'key_unica',
            'description' => 'Descricao 1',
            'price_suggested_cents' => 5000,
            'status' => 'active',
            'version_min' => '1.0.0',
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        Module::create([
            'name' => 'Modulo Teste 2',
            'code' => 'key_unica', // Duplicada
            'description' => 'Descricao 2',
            'price_suggested_cents' => 6000,
            'status' => 'active',
            'version_min' => '1.0.0',
        ]);
    }

    public function test_it_can_define_dependencies_for_modules()
    {
        $module = Module::create([
            'name' => 'Modulo Cozinha',
            'code' => 'kitchen',
            'description' => 'Cozinha',
            'price_suggested_cents' => 4500,
            'status' => 'active',
            'version_min' => '1.0.0',
            'dependencies' => ['printing'], // Depende de impressao
        ]);

        $this->assertDatabaseHas('modules', [
            'code' => 'kitchen',
        ]);

        $retrieved = Module::where('code', 'kitchen')->first();
        $this->assertEquals(['printing'], $retrieved->dependencies);
    }

    public function test_it_can_list_active_modules_via_api()
    {
        Module::create([
            'name' => 'Modulo Ativo',
            'code' => 'mod_ativo',
            'description' => 'Ativo',
            'price_suggested_cents' => 3000,
            'status' => 'active',
            'version_min' => '1.0.0',
        ]);

        Module::create([
            'name' => 'Modulo Inativo',
            'code' => 'mod_inativo',
            'description' => 'Inativo',
            'price_suggested_cents' => 3000,
            'status' => 'inactive',
            'version_min' => '1.0.0',
        ]);

        // Faz request ao endpoint do manager
        $response = $this->getJson('/api/modules');

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => 'mod_ativo'])
            ->assertJsonMissing(['code' => 'mod_inativo']);
    }

    public function test_it_can_update_suggested_price_of_a_module()
    {
        $module = Module::create([
            'name' => 'Modulo Preço',
            'code' => 'mod_preco',
            'description' => 'Preço',
            'price_suggested_cents' => 3000,
            'status' => 'active',
            'version_min' => '1.0.0',
        ]);

        $module->update(['price_suggested_cents' => 3500]);

        $this->assertDatabaseHas('modules', [
            'code' => 'mod_preco',
            'price_suggested_cents' => 3500,
        ]);
    }
}
