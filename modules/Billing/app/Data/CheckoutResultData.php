<?php

namespace Modules\Billing\Data;

use Spatie\LaravelData\Data;

class CheckoutResultData extends Data
{
    public function __construct(
        public string $sessionId,
        public string $url,
        public string $provider,
    ) {}
}
