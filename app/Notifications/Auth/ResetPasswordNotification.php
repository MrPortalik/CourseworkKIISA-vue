<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Сброс пароля — '.config('app.name'))
            ->greeting('Здравствуйте!')
            ->line('Вы получили это письмо, потому что для вашего аккаунта запрошен сброс пароля.')
            ->action('Сбросить пароль', $url)
            ->line('Ссылка действительна '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' минут.')
            ->line('Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.');
    }
}
