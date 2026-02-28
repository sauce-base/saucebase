<?php

namespace Modules\Billing\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Events\PaymentFailed;
use Modules\Billing\Events\PaymentSucceeded;
use Modules\Billing\Events\SubscriptionCancelled;
use Modules\Billing\Events\SubscriptionCreated;
use Modules\Billing\Events\SubscriptionResumed;
use Modules\Billing\Events\SubscriptionUpdated;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Price;
use Modules\Billing\Models\Product;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Notifications\PaymentFailedNotification;
use Modules\Billing\Notifications\PaymentSucceededNotification;
use Modules\Billing\Notifications\SubscriptionCancelledNotification;
use Modules\Billing\Notifications\SubscriptionCreatedNotification;
use Modules\Billing\Notifications\SubscriptionResumedNotification;
use Modules\Billing\Notifications\SubscriptionUpdatedNotification;
use Tests\TestCase;

class BillingNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_subscription_created_sends_notification(): void
    {
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new SubscriptionCreated($subscription));

        Notification::assertSentTo($this->user, SubscriptionCreatedNotification::class);
    }

    public function test_subscription_created_email_contains_product_name(): void
    {
        $product = Product::factory()->create(['name' => 'Pro Plan']);
        $price = Price::factory()->create([
            'product_id' => $product->id,
            'amount' => 2999,
            'currency' => Currency::USD,
            'interval' => 'month',
        ]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
        ]);

        $notification = new SubscriptionCreatedNotification($subscription);
        $mail = $notification->toMail($this->user);

        $this->assertEquals('Welcome to Pro Plan', $mail->subject);
        $this->assertStringContainsString('Pro Plan', $mail->introLines[0]);
        $this->assertEquals('Go to Dashboard', $mail->actionText);
    }

    public function test_subscription_created_email_contains_price_info(): void
    {
        $product = Product::factory()->create(['name' => 'Starter Plan']);
        $price = Price::factory()->create([
            'product_id' => $product->id,
            'amount' => 999,
            'currency' => Currency::USD,
            'interval' => 'month',
        ]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
        ]);

        $notification = new SubscriptionCreatedNotification($subscription);
        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('$9.99/month', $mail->introLines[1]);
    }

    public function test_subscription_cancelled_sends_notification(): void
    {
        $subscription = Subscription::factory()->cancelled()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new SubscriptionCancelled($subscription));

        Notification::assertSentTo($this->user, SubscriptionCancelledNotification::class);
    }

    public function test_subscription_cancelled_email_contains_end_date(): void
    {
        $product = Product::factory()->create(['name' => 'Pro Plan']);
        $price = Price::factory()->create(['product_id' => $product->id]);
        $endsAt = now()->addDays(30);
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
            'ends_at' => $endsAt,
        ]);

        $notification = new SubscriptionCancelledNotification($subscription);
        $mail = $notification->toMail($this->user);

        $this->assertEquals('Subscription Cancelled', $mail->subject);
        $this->assertStringContainsString($endsAt->format('F j, Y'), $mail->introLines[1]);
        $this->assertEquals('Manage Billing', $mail->actionText);
    }

    public function test_payment_succeeded_sends_notification(): void
    {
        $payment = Payment::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new PaymentSucceeded($payment));

        Notification::assertSentTo($this->user, PaymentSucceededNotification::class);
    }

    public function test_payment_succeeded_email_contains_amount(): void
    {
        $payment = Payment::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 2999,
            'currency' => Currency::USD,
        ]);

        $notification = new PaymentSucceededNotification($payment);
        $mail = $notification->toMail($this->user);

        $this->assertEquals('Payment Received', $mail->subject);
        $this->assertStringContainsString('$29.99', $mail->introLines[0]);
    }

    public function test_payment_succeeded_email_shows_one_time_purchase_for_non_subscription(): void
    {
        $product = Product::factory()->create(['name' => 'Lifetime Access']);
        $price = Price::factory()->create([
            'product_id' => $product->id,
            'amount' => 29900,
            'interval' => null,
        ]);
        $payment = Payment::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
            'subscription_id' => null,
            'amount' => 29900,
            'currency' => Currency::USD,
        ]);

        $notification = new PaymentSucceededNotification($payment);
        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('Lifetime Access', $mail->introLines[0]);
        $this->assertStringContainsString('one-time purchase', $mail->introLines[0]);
    }

    public function test_payment_succeeded_email_does_not_show_one_time_for_subscription(): void
    {
        $product = Product::factory()->create(['name' => 'Pro Plan']);
        $price = Price::factory()->create([
            'product_id' => $product->id,
            'amount' => 2999,
            'interval' => 'month',
        ]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
        ]);
        $payment = Payment::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
            'subscription_id' => $subscription->id,
            'amount' => 2999,
            'currency' => Currency::USD,
        ]);

        $notification = new PaymentSucceededNotification($payment);
        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('Pro Plan', $mail->introLines[0]);
        $this->assertStringNotContainsString('one-time purchase', $mail->introLines[0]);
    }

    public function test_payment_succeeded_email_shows_renewal_date_for_subscription(): void
    {
        $product = Product::factory()->create(['name' => 'Pro Plan']);
        $price = Price::factory()->create([
            'product_id' => $product->id,
            'amount' => 2999,
            'interval' => 'month',
        ]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
            'current_period_ends_at' => '2026-03-12 00:00:00',
        ]);
        $payment = Payment::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
            'subscription_id' => $subscription->id,
            'amount' => 2999,
            'currency' => Currency::USD,
        ]);

        $notification = new PaymentSucceededNotification($payment);
        $mail = $notification->toMail($this->user);

        $this->assertStringContainsString('March 12, 2026', $mail->introLines[1]);
        $this->assertStringContainsString('next billing date', $mail->introLines[1]);
    }

    public function test_payment_succeeded_email_omits_renewal_date_when_not_available(): void
    {
        $product = Product::factory()->create(['name' => 'Pro Plan']);
        $price = Price::factory()->create([
            'product_id' => $product->id,
            'amount' => 2999,
            'interval' => 'month',
        ]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
            'current_period_ends_at' => null,
        ]);
        $payment = Payment::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
            'subscription_id' => $subscription->id,
            'amount' => 2999,
            'currency' => Currency::USD,
        ]);

        $notification = new PaymentSucceededNotification($payment);
        $mail = $notification->toMail($this->user);

        $this->assertCount(1, $mail->introLines);
    }

    public function test_payment_succeeded_email_links_to_invoice_when_available(): void
    {
        $payment = Payment::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'payment_id' => $payment->id,
            'hosted_invoice_url' => 'https://stripe.com/invoice/123',
        ]);

        $notification = new PaymentSucceededNotification($payment);
        $mail = $notification->toMail($this->user);

        $this->assertEquals('View Invoice', $mail->actionText);
        $this->assertEquals('https://stripe.com/invoice/123', $mail->actionUrl);
    }

    public function test_payment_succeeded_email_links_to_billing_when_no_invoice(): void
    {
        $payment = Payment::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $notification = new PaymentSucceededNotification($payment);
        $mail = $notification->toMail($this->user);

        $this->assertEquals('Go to Billing', $mail->actionText);
    }

    public function test_payment_failed_sends_notification(): void
    {
        $payment = Payment::factory()->failed()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new PaymentFailed($payment));

        Notification::assertSentTo($this->user, PaymentFailedNotification::class);
    }

    public function test_payment_failed_email_contains_amount_and_reason(): void
    {
        $payment = Payment::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 4999,
            'currency' => Currency::USD,
            'failure_message' => 'Your card was declined.',
        ]);

        $notification = new PaymentFailedNotification($payment);
        $mail = $notification->toMail($this->user);

        $this->assertEquals('Payment Failed', $mail->subject);
        $this->assertStringContainsString('$49.99', $mail->introLines[0]);
        $this->assertStringContainsString('Your card was declined.', $mail->introLines[1]);
        $this->assertEquals('Update Payment Method', $mail->actionText);
    }

    public function test_payment_failed_email_omits_reason_when_null(): void
    {
        $payment = Payment::factory()->create([
            'customer_id' => $this->customer->id,
            'amount' => 4999,
            'currency' => Currency::USD,
            'failure_message' => null,
        ]);

        $notification = new PaymentFailedNotification($payment);
        $mail = $notification->toMail($this->user);

        $this->assertCount(2, $mail->introLines);
    }

    public function test_subscription_resumed_sends_notification(): void
    {
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new SubscriptionResumed($subscription));

        Notification::assertSentTo($this->user, SubscriptionResumedNotification::class);
    }

    public function test_subscription_resumed_email_contains_product_name(): void
    {
        $product = Product::factory()->create(['name' => 'Pro Plan']);
        $price = Price::factory()->create(['product_id' => $product->id]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
        ]);

        $notification = new SubscriptionResumedNotification($subscription);
        $mail = $notification->toMail($this->user);

        $this->assertEquals('Subscription Resumed', $mail->subject);
        $this->assertStringContainsString('Pro Plan', $mail->introLines[0]);
        $this->assertEquals('Manage Billing', $mail->actionText);
    }

    public function test_subscription_updated_sends_notification_for_cancellation_pending(): void
    {
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
            'cancelled_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        event(new SubscriptionUpdated($subscription));

        Notification::assertSentTo($this->user, SubscriptionUpdatedNotification::class);
    }

    public function test_subscription_updated_skips_notification_for_active_subscription(): void
    {
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        event(new SubscriptionUpdated($subscription));

        Notification::assertNotSentTo($this->user, SubscriptionUpdatedNotification::class);
    }

    public function test_subscription_updated_email_shows_cancellation_pending(): void
    {
        $product = Product::factory()->create(['name' => 'Pro Plan']);
        $price = Price::factory()->create(['product_id' => $product->id]);
        $endsAt = now()->addDays(30);
        $subscription = Subscription::factory()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
            'cancelled_at' => now(),
            'ends_at' => $endsAt,
        ]);

        $notification = new SubscriptionUpdatedNotification($subscription);
        $mail = $notification->toMail($this->user);

        $this->assertEquals('Subscription Cancellation Scheduled', $mail->subject);
        $this->assertStringContainsString('Pro Plan', $mail->introLines[0]);
        $this->assertStringContainsString($endsAt->format('F j, Y'), $mail->introLines[0]);
    }

    public function test_subscription_updated_email_shows_past_due(): void
    {
        $product = Product::factory()->create(['name' => 'Pro Plan']);
        $price = Price::factory()->create(['product_id' => $product->id]);
        $subscription = Subscription::factory()->pastDue()->create([
            'customer_id' => $this->customer->id,
            'price_id' => $price->id,
        ]);

        $notification = new SubscriptionUpdatedNotification($subscription);
        $mail = $notification->toMail($this->user);

        $this->assertEquals('Subscription Past Due', $mail->subject);
        $this->assertStringContainsString('Pro Plan', $mail->introLines[0]);
        $this->assertStringContainsString('past due', $mail->introLines[0]);
    }

    public function test_notifications_use_mail_channel(): void
    {
        $subscription = Subscription::factory()->create(['customer_id' => $this->customer->id]);
        $payment = Payment::factory()->create(['customer_id' => $this->customer->id]);

        $this->assertEquals(['mail'], (new SubscriptionCreatedNotification($subscription))->via($this->user));
        $this->assertEquals(['mail'], (new SubscriptionCancelledNotification($subscription))->via($this->user));
        $this->assertEquals(['mail'], (new SubscriptionResumedNotification($subscription))->via($this->user));
        $this->assertEquals(['mail'], (new SubscriptionUpdatedNotification($subscription))->via($this->user));
        $this->assertEquals(['mail'], (new PaymentSucceededNotification($payment))->via($this->user));
        $this->assertEquals(['mail'], (new PaymentFailedNotification($payment))->via($this->user));
    }
}
