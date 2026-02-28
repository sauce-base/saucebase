<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Billing\Enums\SubscriptionStatus;

/**
 * @property int $id
 * @property int $customer_id
 * @property int $price_id
 * @property int|null $payment_method_id
 * @property string|null $provider_subscription_id
 * @property \Modules\Billing\Enums\SubscriptionStatus $status
 * @property \Carbon\Carbon|null $trial_starts_at
 * @property \Carbon\Carbon|null $trial_ends_at
 * @property \Carbon\Carbon|null $current_period_starts_at
 * @property \Carbon\Carbon|null $current_period_ends_at
 * @property \Carbon\Carbon|null $cancelled_at
 * @property \Carbon\Carbon|null $ends_at
 * @property array<string, mixed>|null $metadata
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'price_id',
        'payment_method_id',
        'provider_subscription_id',
        'status',
        'trial_starts_at',
        'trial_ends_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'cancelled_at',
        'ends_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'trial_starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'current_period_starts_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'ends_at' => 'datetime',
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
     * @return BelongsTo<Price, $this>
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    /**
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
