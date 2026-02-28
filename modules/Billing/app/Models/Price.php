<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Billing\Enums\BillingScheme;
use Modules\Billing\Enums\Currency;

/**
 * @property int $id
 * @property int $product_id
 * @property int|null $payment_provider_id
 * @property string|null $provider_price_id
 * @property \Modules\Billing\Enums\Currency $currency
 * @property int $amount
 * @property \Modules\Billing\Enums\BillingScheme $billing_scheme
 * @property string|null $interval
 * @property int|null $interval_count
 * @property array<string, mixed>|null $metadata
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'payment_provider_id',
        'provider_price_id',
        'currency',
        'amount',
        'billing_scheme',
        'interval',
        'interval_count',
        'metadata',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'interval_count' => 'integer',
            'currency' => Currency::class,
            'billing_scheme' => BillingScheme::class,
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<PaymentProvider, $this>
     */
    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }
}
