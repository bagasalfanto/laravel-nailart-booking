<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordToEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $input = strtolower(trim((string) $request->input('email')));

        // Cari user via email primary ATAU backup email yang sudah verified.
        $user = User::query()
            ->where('email', $input)
            ->orWhere(function ($q) use ($input) {
                $q->where('backup_email', $input)
                    ->whereNotNull('backup_email_verified_at');
            })
            ->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Email Tidak Terdaftar',
                    'message' => 'Email yang Anda masukkan tidak ditemukan di sistem.',
                ]);
        }

        // Generate token + kirim notif ke email yang user input
        // (kalau backup, link diterima di backup; kalau primary, ke primary).
        $token = Password::broker()->createToken($user);
        $user->notify(new ResetPasswordToEmail($token, $input));

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'Link Dikirim',
            'message' => 'Link reset password telah dikirim. Periksa inbox dan folder spam Anda.',
        ]);
    }
}
