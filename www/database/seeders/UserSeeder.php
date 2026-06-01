<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::count() === 0) {
            User::create([
                'uuid' => 'a8b381a8-129b-43d9-a9a3-a5c7c2512f22',
                'name' => 'Alessandro Dev',
                'email' => 'admin@comanda.com.br',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'status' => UserStatusEnum::ACTIVE,
            ]);
        }
    }
}
