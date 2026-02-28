<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Enums\InvoiceStatus;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $subscription_id
 * @property int|null $payment_id
 * @property string|null $provider_invoice_id
 * @property string|null $number
 * @property \Modules\Billing\Enums\Currency $currency
 * @property int $subtotal
 * @property int $tax
 * @property int $total
 * @property \Modules\Billing\Enums\InvoiceStatus $status
 * @property \Carbon\Carbon|null $due_at
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\Carbon|null $voided_at
 * @property string|null $hosted_invoice_url
 * @property string|null $pdf_url
 * @property array<string, mixed>|null $metadata
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'subscription_id',
        'payment_id',
        'provider_invoice_id',
        'number',
        'currency',
        'subtotal',
        'tax',
        'total',
        'status',
        'due_at',
        'paid_at',
        'voided_at',
        'hosted_invoice_url',
        'pdf_url',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'tax' => 'integer',
            'total' => 'integer',
            'currency' => Currency::class,
            'status' => InvoiceStatus::class,
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
