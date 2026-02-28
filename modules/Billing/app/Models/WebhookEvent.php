<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $provider_event_id
 * @property string $provider
 * @property string|null $type
 * @property \Carbon\Carbon|null $processed_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class WebhookEvent extends Model
{
    protected $fillable = [
        'provider_event_id',
        'provider',
        'type',
        'processed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }
}
