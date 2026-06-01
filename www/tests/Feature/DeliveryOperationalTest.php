<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Customer;
use App\Models\DeliveryFee;
use App\Models\DeliveryOrder;
use App\Models\DeliveryTracking;
use App\Models\DeliveryZone;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeliveryOperationalTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_delivery_zones_and_fees()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);

        $zone = DeliveryZone::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'name' => 'Zona Sul',
            'type' => 'bairro',
            'zone_data' => ['bairros' => ['Ipanema', 'Copacabana']],
            'is_active' => true,
        ]);

        $fee = DeliveryFee::create([
            'delivery_zone_id' => $zone->id,
            'price' => 15.00,
            'estimated_minutes' => 45,
        ]);

        $this->assertDatabaseHas('delivery_zones', [
            'name' => 'Zona Sul',
            'type' => 'bairro',
        ]);

        $this->assertDatabaseHas('delivery_fees', [
            'delivery_zone_id' => $zone->id,
            'price' => 15.00,
            'estimated_minutes' => 45,
        ]);

        $this->assertEquals(1, $zone->fees()->count());
    }

    #[Test]
    public function it_can_create_a_delivery_order_associated_to_a_customer()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $session = OrderSession::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'opened_by_employee_id' => $employee->id,
            'people_count' => 1,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $order = Order::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'session_id' => $session->id,
            'employee_id' => $employee->id,
            'order_number' => 'ORD-1001',
            'total_cents' => 5000,
            'status' => 'draft',
        ]);

        $zone = DeliveryZone::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'name' => 'Zona Centro',
            'type' => 'raio',
            'zone_data' => ['km' => 5],
        ]);

        $deliveryOrder = DeliveryOrder::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'delivery_zone_id' => $zone->id,
            'recipient_name' => 'John Doe',
            'recipient_phone' => '11999999999',
            'street' => 'Av Paulista',
            'number' => '1000',
            'complement' => 'Apto 12',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01310-100',
            'delivery_fee' => 10.00,
            'status' => 'pending',
            'tracking_code' => 'track_12345',
        ]);

        $this->assertDatabaseHas('delivery_orders', [
            'recipient_name' => 'John Doe',
            'zip_code' => '01310-100',
            'status' => 'pending',
        ]);

        $this->assertEquals($order->id, $deliveryOrder->order->id);
    }

    #[Test]
    public function it_can_track_delivery_location_updates()
    {
        $company = Company::factory()->create();
        $unit = CompanyUnit::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $employee = Employee::factory()->create(['company_id' => $company->id]);

        $session = OrderSession::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'opened_by_employee_id' => $employee->id,
            'people_count' => 1,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $order = Order::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'session_id' => $session->id,
            'employee_id' => $employee->id,
            'order_number' => 'ORD-1002',
            'total_cents' => 6000,
            'status' => 'draft',
        ]);

        $deliveryOrder = DeliveryOrder::create([
            'company_id' => $company->id,
            'unit_id' => $unit->id,
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'recipient_name' => 'Jane Doe',
            'recipient_phone' => '11988888888',
            'street' => 'Av Rebouças',
            'number' => '500',
            'neighborhood' => 'Pinheiros',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '05401-000',
            'delivery_fee' => 12.00,
            'status' => 'assigned',
        ]);

        $tracking = DeliveryTracking::create([
            'delivery_order_id' => $deliveryOrder->id,
            'latitude' => -23.561234,
            'longitude' => -46.671234,
            'status' => 'dispatched',
            'notes' => 'Saiu para entrega',
        ]);

        $this->assertDatabaseHas('delivery_trackings', [
            'delivery_order_id' => $deliveryOrder->id,
            'status' => 'dispatched',
            'latitude' => -23.561234,
        ]);

        $this->assertEquals(1, $deliveryOrder->trackings()->count());
    }
}
