<?php

namespace Modules\Billing\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;

class PaymentSucceededNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $currency = $this->payment->currency ?? Currency::default();
        $amount = $currency->formatAmount($this->payment->amount);
        $isOneTime = $this->payment->subscription_id === null;
        $productName = $this->payment->price?->product->name
            ?? $this->payment->subscription?->price->product->name
            ?? __('your subscription');

        $invoice = Invoice::where('payment_id', $this->payment->id)->first();
        $actionUrl = $invoice?->hosted_invoice_url ?? route('settings.billing'); // @phpstan-ignore nullsafe.neverNull
        $actionText = $invoice?->hosted_invoice_url ? __('View Invoice') : __('Go to Billing');

        $line = $isOneTime
            ? __("We've received your payment of **:amount** for **:product** (one-time purchase).", ['amount' => $amount, 'product' => $productName])
            : __("We've received your payment of **:amount** for **:product**.", ['amount' => $amount, 'product' => $productName]);

        $renewsAt = $this->payment->subscription?->current_period_ends_at;

        $mail = (new MailMessage)
            ->subject(__('Payment Received'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line($line);

        if (! $isOneTime && $renewsAt) {
            $mail->line(__('Your next billing date is **:date**.', ['date' => $renewsAt->translatedFormat('F j, Y')]));
        }

        return $mail
            ->action($actionText, $actionUrl)
            ->line(__('Thank you for your payment!'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
        ];
    }
}
