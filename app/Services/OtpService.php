<?php

namespace App\Services;

use App\Mail\OtpVerificationMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public const CODE_LENGTH      = 6;
    public const EXPIRY_MINUTES   = 15;
    public const RESEND_COOLDOWN  = 30;   // detik
    public const MAX_ATTEMPTS     = 5;

    /**
     * Generate OTP baru, kirim email, simpan hash ke DB.
     * Otomatis invalidate OTP user yang masih aktif sebelumnya.
     */
    public function generateAndSend(User $user): EmailOtp
    {
        $this->invalidateActive($user);

        $code = $this->randomCode();

        $otp = EmailOtp::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            'attempts' => 0,
            'used_at' => null,
            'last_sent_at' => now(),
        ]);

        Mail::to($user->email)->send(new OtpVerificationMail($user, $code, self::EXPIRY_MINUTES));

        return $otp;
    }

    /**
     * Verifikasi code yang diinput user.
     * Return true jika sukses, false jika gagal (sudah handle attempts counter).
     */
    public function verify(User $user, string $code): bool
    {
        $otp = $this->latestActive($user);

        if (! $otp || ! $otp->isValid()) {
            return false;
        }

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');
            return false;
        }

        $otp->update(['used_at' => now()]);

        $user->forceFill([
            'email_verified_at' => now(),
            'status'            => 'active',
        ])->save();

        return true;
    }

    /**
     * Cek apakah user boleh request OTP baru (cooldown 30 detik).
     */
    public function canResend(User $user): bool
    {
        $otp = $this->latestActive($user);
        if (! $otp || ! $otp->last_sent_at) {
            return true;
        }

        return $otp->last_sent_at->diffInSeconds(now()) >= self::RESEND_COOLDOWN;
    }

    /**
     * Berapa detik lagi sampai user boleh resend.
     */
    public function secondsUntilResend(User $user): int
    {
        $otp = $this->latestActive($user);
        if (! $otp || ! $otp->last_sent_at) {
            return 0;
        }

        $elapsed = $otp->last_sent_at->diffInSeconds(now());

        return max(0, self::RESEND_COOLDOWN - (int) $elapsed);
    }

    public function invalidateActive(User $user): void
    {
        EmailOtp::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    private function latestActive(User $user): ?EmailOtp
    {
        return EmailOtp::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->latest()
            ->first();
    }

    private function randomCode(): string
    {
        return str_pad((string) random_int(1, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }
}
