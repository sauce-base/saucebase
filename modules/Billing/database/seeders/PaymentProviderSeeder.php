<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\PaymentProvider;

class PaymentProviderSeeder extends Seeder
{
    public function run(): void
    {
        PaymentProvider::firstOrCreate([
            'name' => 'Stripe',
            'slug' => 'stripe',
            'config' => null,
            'is_active' => true,
        ]);
    }
}
