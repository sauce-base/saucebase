<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

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
        $changedAt = now()->isoFormat('LLL');

        $mail = (new MailMessage)
            ->subject(__('Password Changed Successfully'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your password was successfully changed.'))
            ->line(__('Change time: :time', ['time' => $changedAt]))
            ->line(__('If you did not make this change, please contact us immediately.'));

        // The auth module sends this too, and auth installs without settings — so the
        // button disappears rather than throwing RouteNotFoundException.
        if (Route::has('settings.profile')) {
            $mail->action(__('View Profile'), route('settings.profile'));
        }

        return $mail->line(__('Thank you for using our application!'));
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
