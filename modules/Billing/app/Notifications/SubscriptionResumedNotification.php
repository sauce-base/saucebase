<?php

namespace Modules\Billing\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Billing\Models\Subscription;

class SubscriptionResumedNotification extends Notification
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

        return (new MailMessage)
            ->subject(__('Subscription Resumed'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your subscription to **:product** has been resumed.', ['product' => $productName]))
            ->action(__('Manage Billing'), route('settings.billing'))
            ->line(__('Welcome back!'));
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
