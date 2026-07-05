<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleAuthService
{
    /**
     * Cocokkan user Google ke akun lokal, atau buat baru.
     *
     * Match strategy (urutan prioritas):
     *  1. google_id sama          → langsung login (returning user)
     *  2. email sama + sudah verified → auto-link google_id (legitimate user yang baru pertama login via Google)
     *  3. email sama + BELUM verified → auto-link + invalidate password lama
     *     (mitigasi: akun mungkin dibuat oleh orang lain yang tidak own email; Google sudah prove ownership)
     *  4. tidak ada match           → buat user baru, langsung aktif (Google sudah verifikasi email)
     */
    public function findOrCreateUser(SocialiteUser $googleUser): User
    {
        return DB::transaction(function () use ($googleUser) {
            $email     = strtolower((string) $googleUser->getEmail());
            $googleId  = (string) $googleUser->getId();
            $name      = (string) ($googleUser->getName() ?: $email);
            $avatarUrl = (string) ($googleUser->getAvatar() ?: '');

            // 1) Match by google_id
            $existing = User::query()->where('google_id', $googleId)->first();
            if ($existing) {
                return $this->refreshGoogleProfile($existing, $name, $avatarUrl);
            }

            // 2 & 3) Match by email — auto-link
            $existing = User::query()->where('email', $email)->first();
            if ($existing) {
                $wasUnverified = ! $existing->hasVerifiedEmail();

                $existing->forceFill([
                    'google_id'         => $googleId,
                    'email_verified_at' => $existing->email_verified_at ?? now(),
                    'status'            => 'active',
                    'avatar'            => $existing->avatar ?: $avatarUrl,
                ])->save();

                // Account belum verified sebelumnya → ada risiko orang lain set password.
                // Reset password ke random; user asli bisa pakai "forgot password" untuk set ulang.
                if ($wasUnverified) {
                    $existing->forceFill(['password' => Hash::make(Str::random(40))])->save();
                }

                return $existing;
            }

            // 4) User baru → buat akun, langsung aktif
            return $this->createUser($email, $googleId, $name, $avatarUrl);
        });
    }

    private function createUser(string $email, string $googleId, string $name, string $avatarUrl): User
    {
        $username = $this->generateUniqueUsername($name, $email);

        $user = User::create([
            'full_name'         => $name,
            'username'          => $username,
            'email'             => $email,
            'avatar'            => $avatarUrl ?: null,
            'phone_number'      => null,
            'google_id'         => $googleId,
            'password'          => Hash::make(Str::random(40)), // random — user akan login via Google
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);

        $user->assignRole('customer');
        $user->customer()->firstOrCreate();

        return $user;
    }

    private function refreshGoogleProfile(User $user, string $name, string $avatarUrl): User
    {
        $payload = [];

        if (empty($user->full_name) && $name !== '') {
            $payload['full_name'] = $name;
        }
        if (empty($user->avatar) && $avatarUrl !== '') {
            $payload['avatar'] = $avatarUrl;
        }
        if (! $user->hasVerifiedEmail()) {
            $payload['email_verified_at'] = now();
            $payload['status']            = 'active';
        }

        if ($payload) {
            $user->forceFill($payload)->save();
        }

        return $user;
    }

    private function generateUniqueUsername(string $name, string $email): string
    {
        $base = Str::slug(Str::lower($name));
        if ($base === '') {
            $base = Str::before($email, '@');
            $base = Str::slug(Str::lower($base));
        }
        if ($base === '') {
            $base = 'user';
        }

        $username = $base;
        $counter  = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.'-'.$counter;
            $counter++;
        }

        return $username;
    }
}
