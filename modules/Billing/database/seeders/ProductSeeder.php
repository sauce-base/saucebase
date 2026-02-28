<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Enums\BillingScheme;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Models\PaymentProvider;
use Modules\Billing\Models\Product;

class ProductSeeder extends Seeder
{
    private ?int $stripeProviderId = null;

    public function run(): void
    {
        $this->stripeProviderId = PaymentProvider::where('slug', 'stripe')->value('id');

        $this->createFreeProduct();
        $this->createProProduct();
        $this->createTeamProduct();
    }

    private function createFreeProduct(): void
    {
        $product = Product::create([
            'sku' => 'free',
            'slug' => 'free',
            'name' => 'Free',
            'description' => 'Get started with the basics',
            'display_order' => 1,
            'is_visible' => true,
            'is_highlighted' => false,
            'is_active' => true,
            'features' => [
                '1 project',
                '500MB storage',
                'Community support',
            ],
            'metadata' => [
                'tagline' => 'For hobbyists',
            ],
        ]);

        $product->prices()->createMany([
            [
                'payment_provider_id' => $this->stripeProviderId,
                'provider_price_id' => 'price_free_monthly',
                'currency' => Currency::default(),
                'amount' => 0,
                'billing_scheme' => BillingScheme::FlatRate,
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
            ],
            [
                'payment_provider_id' => $this->stripeProviderId,
                'provider_price_id' => 'price_free_yearly',
                'currency' => Currency::default(),
                'amount' => 0,
                'billing_scheme' => BillingScheme::FlatRate,
                'interval' => 'year',
                'interval_count' => 1,
                'is_active' => true,
            ],
        ]);
    }

    private function createProProduct(): void
    {
        $product = Product::create([
            'sku' => 'pro',
            'slug' => 'pro',
            'name' => 'Pro',
            'description' => 'Everything you need to work independently',
            'display_order' => 3,
            'is_visible' => true,
            'is_highlighted' => true,
            'is_active' => true,
            'features' => [
                'Unlimited projects',
                '50GB storage',
                'Priority email support',
                'Advanced analytics',
                'API access',
                'Custom domains',
            ],
            'metadata' => [
                'badge' => 'Most Popular',
                'tagline' => 'For professionals',
            ],
        ]);

        $product->prices()->createMany([
            [
                'payment_provider_id' => $this->stripeProviderId,
                'provider_price_id' => 'price_1SyadREx2sHJcHgwCt0ReZEJ',
                'currency' => Currency::default(),
                'amount' => 2900,
                'billing_scheme' => BillingScheme::FlatRate,
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
            ],
            [
                'payment_provider_id' => $this->stripeProviderId,
                'provider_price_id' => 'price_1SyaCQEx2sHJcHgwEC9VmwSZ',
                'currency' => Currency::default(),
                'amount' => 29000,
                'billing_scheme' => BillingScheme::FlatRate,
                'interval' => 'year',
                'interval_count' => 1,
                'is_active' => true,
                'metadata' => [
                    'badge' => 'Save 17%',
                    'label' => 'Billed annually',
                    'original_price' => '34800',
                ],
            ],
            [
                'payment_provider_id' => $this->stripeProviderId,
                'provider_price_id' => 'price_1SyaajEx2sHJcHgwMC3qb0c6',
                'currency' => Currency::default(),
                'amount' => 29900,
                'billing_scheme' => BillingScheme::FlatRate,
                'interval' => null,
                'interval_count' => null,
                'is_active' => false,
            ],
        ]);
    }

    private function createTeamProduct(): void
    {
        $product = Product::create([
            'sku' => 'team',
            'slug' => 'team',
            'name' => 'Team',
            'description' => 'Collaborate with your team, up to 25 members',
            'display_order' => 4,
            'is_visible' => true,
            'is_highlighted' => false,
            'is_active' => true,
            'features' => [
                'Everything in Pro',
                'Up to 25 team members',
                '200GB shared storage',
                'Team roles & permissions',
                'Priority support',
                'Shared dashboards',
                'Audit logs',
            ],
            'metadata' => [
                'tagline' => 'For teams',
            ],
        ]);

        $product->prices()->createMany([
            [
                'payment_provider_id' => $this->stripeProviderId,
                'provider_price_id' => 'price_1SyaL0Ex2sHJcHgwWaaTGLgo',
                'currency' => Currency::default(),
                'amount' => 7900,
                'billing_scheme' => BillingScheme::FlatRate,
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
            ],
            [
                'payment_provider_id' => $this->stripeProviderId,
                'provider_price_id' => 'price_1SyaLWEx2sHJcHgw3fQdYV0J',
                'currency' => Currency::default(),
                'amount' => 79000,
                'billing_scheme' => BillingScheme::FlatRate,
                'interval' => 'year',
                'interval_count' => 1,
                'is_active' => true,
                'metadata' => [
                    'badge' => 'Save 17%',
                    'label' => 'Billed annually',
                    'original_price' => '94800',
                ],
            ],
            [
                'payment_provider_id' => $this->stripeProviderId,
                'provider_price_id' => 'price_1SyaXDEx2sHJcHgwt74dHzHh',
                'currency' => Currency::default(),
                'amount' => 79900,
                'billing_scheme' => BillingScheme::FlatRate,
                'interval' => null,
                'interval_count' => null,
                'is_active' => false,
            ],
        ]);
    }
}
