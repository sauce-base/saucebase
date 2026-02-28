<?php

namespace Modules\Billing\Contracts;

use Illuminate\Http\Request;
use Modules\Billing\Data\CheckoutData;
use Modules\Billing\Data\CheckoutResultData;
use Modules\Billing\Data\CustomerData;
use Modules\Billing\Data\WebhookData;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Subscription;

interface PaymentGatewayInterface
{
    public function createCustomer(CustomerData $data): Customer;

    public function createCheckoutSession(CheckoutData $data): CheckoutResultData;

    public function cancelSubscription(Subscription $subscription, bool $immediately = false): ?\DateTimeInterface;

    public function resumeSubscription(Subscription $subscription): void;

    public function getManagementUrl(Customer $customer): string;

    public function verifyAndParseWebhook(Request $request): WebhookData;
}
