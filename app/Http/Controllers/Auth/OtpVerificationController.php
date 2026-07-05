<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpVerificationController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route($user->homeRoute());
        }

        return view('auth.verify-otp', [
            'email' => $user->email,
            'cooldownSeconds' => $this->otp->secondsUntilResend($user),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $user = $request->user();

        $key = 'otp-verify:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'code' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! $this->otp->verify($user, (string) $request->input('code'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'code' => 'Kode salah, kadaluarsa, atau sudah pernah digunakan.',
            ]);
        }

        RateLimiter::clear($key);

        return redirect()
            ->route($user->homeRoute())
            ->with('toast', ['type' => 'success', 'title' => 'Email Terverifikasi', 'message' => 'Selamat datang! Email kamu berhasil diverifikasi.']);
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route($user->homeRoute());
        }

        if (! $this->otp->canResend($user)) {
            $wait = $this->otp->secondsUntilResend($user);

            return back()->with('toast', ['type' => 'warning', 'title' => 'Tunggu Sebentar', 'message' => "Tunggu {$wait} detik sebelum minta kode baru."]);
        }

        $this->otp->generateAndSend($user);

        return back()->with('toast', ['type' => 'success', 'title' => 'Kode Dikirim', 'message' => 'Kode verifikasi baru sudah dikirim ke email kamu.']);
    }
}
