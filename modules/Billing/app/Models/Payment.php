<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Enums\PaymentStatus;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $subscription_id
 * @property int|null $payment_method_id
 * @property int|null $price_id
 * @property string|null $provider_payment_id
 * @property \Modules\Billing\Enums\Currency $currency
 * @property int $amount
 * @property int $amount_refunded
 * @property \Modules\Billing\Enums\PaymentStatus $status
 * @property string|null $failure_code
 * @property string|null $failure_message
 * @property array<string, mixed>|null $metadata
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'subscription_id',
        'payment_method_id',
        'price_id',
        'provider_payment_id',
        'currency',
        'amount',
        'amount_refunded',
        'status',
        'failure_code',
        'failure_message',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'amount_refunded' => 'integer',
            'currency' => Currency::class,
            'status' => PaymentStatus::class,
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
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return BelongsTo<Price, $this>
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }
}
