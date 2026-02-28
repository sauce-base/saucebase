<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Billing\Enums\PaymentMethodType;

/**
 * @property int $id
 * @property int $customer_id
 * @property string|null $provider_payment_method_id
 * @property PaymentMethodType $type
 * @property array<string, mixed>|null $details
 * @property array<string, mixed>|null $metadata
 * @property bool $is_default
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'provider_payment_method_id',
        'type',
        'details',
        'metadata',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PaymentMethodType::class,
            'details' => 'array',
            'metadata' => 'array',
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
