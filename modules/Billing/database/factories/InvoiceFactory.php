<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Enums\InvoiceStatus;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Invoice;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Billing\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'subscription_id' => null,
            'payment_id' => null,
            'provider_invoice_id' => 'in_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'number' => 'INV-'.fake()->unique()->numerify('######'),
            'currency' => Currency::default(),
            'subtotal' => 999,
            'tax' => 0,
            'total' => 999,
            'status' => InvoiceStatus::Paid,
            'due_at' => now()->addDays(30),
            'paid_at' => now(),
            'voided_at' => null,
            'hosted_invoice_url' => null,
            'pdf_url' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the invoice is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Draft,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the invoice is posted.
     */
    public function posted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Posted,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the invoice is unpaid.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Unpaid,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the invoice is voided.
     */
    public function voided(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Voided,
            'paid_at' => null,
            'voided_at' => now(),
        ]);
    }

    /**
     * Indicate that the invoice includes tax.
     */
    public function withTax(): static
    {
        return $this->state(function (array $attributes) {
            $subtotal = $attributes['subtotal'] ?? 999;
            $tax = (int) ($subtotal * 0.1);

            return [
                'tax' => $tax,
                'total' => $subtotal + $tax,
            ];
        });
    }
}
