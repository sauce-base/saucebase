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
        // isoFormat rather than format: the latter hardcodes English month and meridiem
        // names no matter what locale the mail is being rendered in.
        $changedAt = now()->isoFormat('LLL');

        $mail = (new MailMessage)
            ->subject(__('Password Changed Successfully'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your password was successfully changed.'))
            ->line(__('Change time: :time', ['time' => $changedAt]))
            ->line(__('If you did not make this change, please contact us immediately.'));

        // The profile page belongs to the settings module, and this notification does not.
        // The auth module sends it too, and auth installs without settings — so the button
        // is a courtesy that disappears rather than a dependency that throws.
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
