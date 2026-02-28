<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Manager;
use Modules\Billing\Contracts\PaymentGatewayInterface;
use Modules\Billing\Services\Gateways\StripeGateway;
use Stripe\StripeClient;

/**
 * @method PaymentGatewayInterface driver(?string $driver = null)
 */
class PaymentGatewayManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('billing.default_gateway', 'stripe');
    }

    public function createStripeDriver(): StripeGateway
    {
        $client = new StripeClient($this->config->get('services.stripe.secret_key'));

        return new StripeGateway($client);
    }
}
