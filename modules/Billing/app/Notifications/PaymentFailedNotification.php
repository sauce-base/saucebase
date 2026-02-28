<?php

namespace Modules\Billing\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Models\Payment;

class PaymentFailedNotification extends Notification
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
        $failureMessage = $this->payment->failure_message;

        $message = (new MailMessage)
            ->subject(__('Payment Failed'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('We were unable to process your payment of **:amount**.', ['amount' => $amount]));

        if ($failureMessage) {
            $message->line(__('Reason: :reason', ['reason' => $failureMessage]));
        }

        return $message
            ->line(__('Please update your payment method to avoid service interruption.'))
            ->action(__('Update Payment Method'), route('settings.billing'));
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
