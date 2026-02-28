<?php

namespace Modules\Billing\Tests\Unit;

use Modules\Billing\Data\WebhookData;
use Modules\Billing\Enums\WebhookEventType;
use PHPUnit\Framework\TestCase;

class WebhookDataTest extends TestCase
{
    public function test_is_returns_true_for_matching_type(): void
    {
        $data = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_123',
            payload: [],
        );

        $this->assertTrue($data->is(WebhookEventType::CheckoutCompleted));
    }

    public function test_is_returns_false_for_non_matching_type(): void
    {
        $data = new WebhookData(
            type: WebhookEventType::CheckoutCompleted,
            provider: 'stripe',
            providerEventId: 'evt_123',
            payload: [],
        );

        $this->assertFalse($data->is(WebhookEventType::SubscriptionUpdated));
    }
}
