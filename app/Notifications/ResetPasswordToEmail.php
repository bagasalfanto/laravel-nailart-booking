<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Reset-password notification — pakai HTML email template kustom (branded).
 * Routing override: kirim ke email tujuan dynamic (primary atau backup).
 */
class ResetPasswordToEmail extends BaseResetPassword
{
    public function __construct(string $token, public string $targetEmail)
    {
        parent::__construct($token);
    }

    public function routeNotificationFor($channel, $notifiable = null): string
    {
        return $this->targetEmail;
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->targetEmail,
        ], false));

        return (new MailMessage)
            ->subject('Reset Password — '.config('app.name'))
            ->view('emails.password-reset', [
                'user'           => $notifiable,
                'resetUrl'       => $url,
                'expiryMinutes'  => config('auth.passwords.users.expire', 60),
                'supportEmail'   => config('mail.from.address') ?: 'support@nailbyhilda.com',
            ]);
    }
}
