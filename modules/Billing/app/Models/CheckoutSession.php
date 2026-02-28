<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Billing\Enums\CheckoutSessionStatus;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $customer_id
 * @property int $price_id
 * @property string|null $provider_session_id
 * @property string|null $success_url
 * @property string|null $cancel_url
 * @property \Modules\Billing\Enums\CheckoutSessionStatus $status
 * @property array<string, mixed>|null $metadata
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class CheckoutSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'customer_id',
        'price_id',
        'provider_session_id',
        'success_url',
        'cancel_url',
        'status',
        'metadata',
        'expires_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (CheckoutSession $session) {
            if (! $session->uuid) {
                $session->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CheckoutSessionStatus::class,
            'metadata' => 'array',
            'expires_at' => 'datetime',
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
}
