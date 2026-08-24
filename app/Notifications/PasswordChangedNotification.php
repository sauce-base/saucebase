<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // isoFormat rather than format: the latter hardcodes English month and meridiem
        // names no matter what locale the mail is being rendered in.
        $changedAt = now()->isoFormat('LLL');

        return (new MailMessage)
            ->subject(__('Password Changed Successfully'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your password was successfully changed.'))
            ->line(__('Change time: :time', ['time' => $changedAt]))
            ->line(__('If you did not make this change, please contact us immediately.'))
            ->action(__('View Profile'), route('settings.profile'))
            ->line(__('Thank you for using our application!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'changed_at' => now()->toIso8601String(),
        ];
    }
}
