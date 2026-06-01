<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\KitchenTicketStatusEnum;
use App\Models\KitchenTicket;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class KitchenTicketFactory extends Factory
{
    protected $model = KitchenTicket::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => KitchenTicketStatusEnum::PENDING,
            'sent_at' => Carbon::now(),
            'started_at' => null,
            'ready_at' => null,
            'completed_at' => null,
        ];
    }
}
