<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyBackupEmail extends Notification
{
    use Queueable;

    public function __construct(
        public string $userId,
        public string $email,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'account.backup-email.verify',
            now()->addHour(),
            ['user' => $this->userId, 'email' => $this->email],
        );

        return (new MailMessage)
            ->subject('Verifikasi Recovery Email — Nailby Bilda')
            ->greeting('Halo!')
            ->line("Anda baru saja menambahkan email ini sebagai recovery email untuk akun Anda di Nailby Bilda.")
            ->line('Klik tombol di bawah ini untuk memverifikasi (link aktif 1 jam).')
            ->action('Verifikasi Recovery Email', $url)
            ->line('Jika Anda tidak melakukan permintaan ini, abaikan email ini.');
    }

    /**
     * Override default routing — kirim ke email yang sedang diverifikasi, bukan email primary.
     */
    public function routeNotificationFor($channel, $notifiable = null): string
    {
        return $this->email;
    }
}
