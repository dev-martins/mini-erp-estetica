<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProfessionalSeeder::class,
            InventorySeeder::class,
            ServiceSeeder::class,
            ClientSeeder::class,
            AppointmentSeeder::class,
            SalesSeeder::class,
            PackageSeeder::class,
            LoyaltySeeder::class,
            MarketingSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
