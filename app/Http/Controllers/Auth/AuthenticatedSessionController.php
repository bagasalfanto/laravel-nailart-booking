<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Belum verifikasi email → paksa ke halaman OTP.
        if (! $user->hasVerifiedEmail()) {
            // Kalau belum ada OTP aktif, generate baru (mis. user lupa close browser, login lagi).
            try {
                if ($this->otp->canResend($user)) {
                    $this->otp->generateAndSend($user);
                }
            } catch (\Throwable $e) {
                Log::error('Gagal kirim OTP saat login', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()
                ->route('verification.otp.notice')
                ->with('toast', [
                    'type' => 'info',
                    'title' => 'Verifikasi Diperlukan',
                    'message' => 'Kode OTP telah dikirim ke email kamu.',
                ]);
        }

        return redirect()
            ->intended(route($user->homeRoute(), absolute: false))
            ->with('toast', [
                'type' => 'success',
                'title' => 'Login Berhasil',
                'message' => 'Selamat datang kembali, '.$user->full_name.'.',
            ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
