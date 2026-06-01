<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_coupon()
    {
        $company = Company::factory()->create();

        $coupon = Coupon::create([
            'company_id' => $company->id,
            'code' => 'PROMO10',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount_cents' => 2000,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('coupons', [
            'code' => 'PROMO10',
            'value' => 10,
        ]);

        $this->assertEquals($company->id, $coupon->company->id);
    }

    #[Test]
    public function it_calculates_percentage_discount_correctly()
    {
        $company = Company::factory()->create();

        $coupon = Coupon::create([
            'company_id' => $company->id,
            'code' => 'PROMO20',
            'type' => 'percentage',
            'value' => 20,
            'min_order_amount_cents' => 1000,
            'is_active' => true,
        ]);

        // R$ 50,00 (5000 centavos) -> 20% desconto = R$ 10,00 (1000 centavos)
        $discount = $coupon->calculateDiscount(5000);
        $this->assertEquals(1000, $discount);
    }

    #[Test]
    public function it_calculates_fixed_discount_correctly()
    {
        $company = Company::factory()->create();

        $coupon = Coupon::create([
            'company_id' => $company->id,
            'code' => 'FIXED15',
            'type' => 'fixed',
            'value' => 1500, // R$ 15,00
            'min_order_amount_cents' => 3000, // R$ 30,00
            'is_active' => true,
        ]);

        $discount = $coupon->calculateDiscount(4000);
        $this->assertEquals(1500, $discount);
    }

    #[Test]
    public function it_respects_max_discount_limit()
    {
        $company = Company::factory()->create();

        $coupon = Coupon::create([
            'company_id' => $company->id,
            'code' => 'PROMO50',
            'type' => 'percentage',
            'value' => 50,
            'max_discount_cents' => 2000, // R$ 20,00 max
            'is_active' => true,
        ]);

        // R$ 100,00 subtotal -> 50% seria R$ 50,00, mas deve limitar a R$ 20,00
        $discount = $coupon->calculateDiscount(10000);
        $this->assertEquals(2000, $discount);
    }

    #[Test]
    public function it_returns_zero_if_coupon_is_inactive()
    {
        $company = Company::factory()->create();

        $coupon = Coupon::create([
            'company_id' => $company->id,
            'code' => 'INACTIVE',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => false,
        ]);

        $discount = $coupon->calculateDiscount(5000);
        $this->assertEquals(0, $discount);
    }

    #[Test]
    public function it_returns_zero_if_coupon_is_expired()
    {
        $company = Company::factory()->create();

        $coupon = Coupon::create([
            'company_id' => $company->id,
            'code' => 'EXPIRED',
            'type' => 'fixed',
            'value' => 1000,
            'expires_at' => Carbon::now()->subDay(),
            'is_active' => true,
        ]);

        $discount = $coupon->calculateDiscount(5000);
        $this->assertEquals(0, $discount);
    }

    #[Test]
    public function it_returns_zero_if_usage_limit_is_reached()
    {
        $company = Company::factory()->create();

        $coupon = Coupon::create([
            'company_id' => $company->id,
            'code' => 'LIMIT',
            'type' => 'percentage',
            'value' => 10,
            'usage_limit' => 5,
            'used_count' => 5,
            'is_active' => true,
        ]);

        $discount = $coupon->calculateDiscount(5000);
        $this->assertEquals(0, $discount);
    }

    #[Test]
    public function it_returns_zero_if_subtotal_is_below_minimum()
    {
        $company = Company::factory()->create();

        $coupon = Coupon::create([
            'company_id' => $company->id,
            'code' => 'MINIMUM',
            'type' => 'fixed',
            'value' => 1000,
            'min_order_amount_cents' => 5000, // R$ 50,00 min
            'is_active' => true,
        ]);

        $discount = $coupon->calculateDiscount(4000); // R$ 40,00 subtotal
        $this->assertEquals(0, $discount);
    }

    #[Test]
    public function it_can_validate_coupon_via_endpoint()
    {
        $company = Company::factory()->create();

        Coupon::create([
            'company_id' => $company->id,
            'code' => 'VALID10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/coupons/validate?code=VALID10&subtotal=5000&company_id={$company->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'discount_cents' => 500,
                'code' => 'VALID10',
            ]);
    }
}
