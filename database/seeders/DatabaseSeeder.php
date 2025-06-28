<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Field;
use App\Models\Membership;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seeder Lapangan
        Field::create([
            'name' => 'Lapangan Sintetis',
            'type' => 'Sintetis',
            'price' => 140000,
            'image' => null,
        ]);
        Field::create([
            'name' => 'Lapangan Vynil',
            'type' => 'Vynil',
            'price' => 150000,
            'image' => null,
        ]);

        // Seeder Paket Membership
        Membership::create([
            'name' => 'Paket 30 Hari',
            'duration' => 30,
            'price' => 500000.00,
        ]);
        Membership::create([
            'name' => 'Paket 60 Hari',
            'duration' => 60,
            'price' => 800000.00,
        ]);
    }
}
