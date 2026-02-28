<?php

namespace Modules\Billing\Enums;

enum WebhookEventType: string
{
    case CheckoutCompleted = 'checkout.completed';
    case SubscriptionUpdated = 'subscription.updated';
    case SubscriptionDeleted = 'subscription.deleted';
    case PaymentSucceeded = 'payment.succeeded';
    case PaymentFailed = 'payment.failed';
    case InvoicePaid = 'invoice.paid';
}
