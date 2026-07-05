<?php

namespace App\Providers;

use App\Listeners\LogAuthEvents;
use App\Models\Activity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });

        // Inject IP + user agent ke setiap activity log otomatis.
        $injectMeta = function ($activity) {
            $request = request();

            $properties = $activity->properties;

            if ($properties instanceof \Illuminate\Support\Collection) {
                $properties = $properties->toArray();
            }
            $properties = is_array($properties) ? $properties : [];

            if (! array_key_exists('ip', $properties) || empty($properties['ip'])) {
                $properties['ip'] = $request?->ip();
            }
            if (! array_key_exists('user_agent', $properties) || empty($properties['user_agent'])) {
                $properties['user_agent'] = substr((string) $request?->userAgent(), 0, 255) ?: null;
            }

            $activity->properties = collect($properties);
        };

        // Daftarkan ke kedua model: spatie default & custom kita — agar pasti ke-trigger.
        Activity::creating($injectMeta);
        \Spatie\Activitylog\Models\Activity::creating($injectMeta);

        // Auth events.
        Event::listen(Login::class,         [LogAuthEvents::class, 'handleLogin']);
        Event::listen(Logout::class,        [LogAuthEvents::class, 'handleLogout']);
        Event::listen(Failed::class,        [LogAuthEvents::class, 'handleFailed']);
        Event::listen(PasswordReset::class, [LogAuthEvents::class, 'handlePasswordReset']);
    }
}
