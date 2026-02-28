<?php

namespace Modules\Billing\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Billing\Models\Subscription;

class SubscriptionCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
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
        $productName = $this->subscription->price->product->name;
        $endsAt = $this->subscription->ends_at?->format('F j, Y') ?? __('the end of your billing period');

        return (new MailMessage)
            ->subject(__('Subscription Cancelled'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your subscription to **:product** has been cancelled.', ['product' => $productName]))
            ->line(__('You will continue to have access until **:date**.', ['date' => $endsAt]))
            ->action(__('Manage Billing'), route('settings.billing'))
            ->line(__('We hope to see you again!'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
        ];
    }
}
