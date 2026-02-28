<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;

class BillingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PaymentProviderSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
