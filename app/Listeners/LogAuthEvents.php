<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

class LogAuthEvents
{
    public function handleLogin(Login $event): void
    {
        activity('auth')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->withProperties(['email' => $event->user->email ?? null])
            ->event('login')
            ->log('User logged in');
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user) return;

        activity('auth')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->withProperties(['email' => $event->user->email ?? null])
            ->event('logout')
            ->log('User logged out');
    }

    public function handleFailed(Failed $event): void
    {
        activity('auth')
            ->withProperties([
                'email'   => $event->credentials['email'] ?? null,
                'reason'  => 'invalid credentials',
            ])
            ->event('login_failed')
            ->log('Login attempt failed');
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        activity('auth')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('password_reset')
            ->log('Password was reset by user');
    }
}
