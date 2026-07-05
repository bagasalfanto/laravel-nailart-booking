<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleCallbackRequest;
use App\Services\GoogleAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthController extends Controller
{
    public function __construct(private readonly GoogleAuthService $google) {}

    /**
     * Redirect ke halaman consent Google.
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Callback setelah user authorize di Google.
     */
    public function callback(GoogleCallbackRequest $request): RedirectResponse
    {
        if ($request->hasError()) {
            return redirect()
                ->route('login')
                ->with('toast', ['type' => 'error', 'title' => 'Login Gagal', 'message' => 'Login Google dibatalkan atau gagal.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect()
                ->route('login')
                ->with('toast', ['type' => 'error', 'title' => 'Koneksi Gagal', 'message' => 'Gagal menghubungkan ke Google. Silakan coba lagi.']);
        }

        if (! $googleUser->getEmail()) {
            return redirect()
                ->route('login')
                ->with('toast', ['type' => 'error', 'title' => 'Email Tidak Ditemukan', 'message' => 'Akun Google kamu tidak menyediakan email.']);
        }

        $user = $this->google->findOrCreateUser($googleUser);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()
            ->intended(route($user->homeRoute(), absolute: false))
            ->with('toast', ['type' => 'success', 'title' => 'Login Berhasil', 'message' => 'Berhasil login dengan Google.']);
    }
}
