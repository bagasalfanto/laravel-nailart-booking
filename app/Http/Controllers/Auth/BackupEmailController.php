<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerifyBackupEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BackupEmailController extends Controller
{
    /**
     * Hanya admin & superadmin yang boleh punya backup email.
     */
    private function ensureRoleAllowed(Request $request): void
    {
        $user = $request->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'superadmin'])) {
            throw new AccessDeniedHttpException('Recovery email hanya tersedia untuk admin dan superadmin.');
        }
    }

    /**
     * Set / ubah backup email — kirim link verifikasi ke email tersebut.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureRoleAllowed($request);
        $user = $request->user();

        $validated = $request->validate([
            'backup_email' => [
                'required', 'email', 'lowercase', 'max:255',
                Rule::notIn([$user->email]),
                // Tidak boleh sama dengan email primary user lain (yang masih aktif).
                Rule::unique(User::class, 'email')->whereNull('deleted_at'),
                // Tidak boleh sama dengan backup email VERIFIED user lain (yang masih aktif).
                Rule::unique(User::class, 'backup_email')
                    ->ignoreModel($user)
                    ->whereNotNull('backup_email_verified_at')
                    ->whereNull('deleted_at'),
            ],
        ], [
            'backup_email.not_in' => 'Recovery email harus berbeda dari email primary.',
            'backup_email.unique' => 'Email tersebut sudah terpakai di akun lain (sebagai email primary atau recovery).',
        ]);

        // Reset status verifikasi (kalau ganti email).
        $user->forceFill([
            'backup_email' => $validated['backup_email'],
            'backup_email_verified_at' => null,
        ])->save();

        Notification::route('mail', $validated['backup_email'])
            ->notify(new VerifyBackupEmail($user->id, $validated['backup_email']));

        activity('auth')
            ->causedBy($user)
            ->withProperties(['backup_email' => $validated['backup_email']])
            ->event('backup_email_set')
            ->log('Backup email set, verification link sent');

        return back()->with('toast', ['type' => 'success', 'title' => 'Verifikasi Dikirim', 'message' => 'Link verifikasi sudah dikirim ke recovery email Anda.']);
    }

    /**
     * Endpoint signed URL — verifikasi backup email.
     */
    public function verify(Request $request, User $user): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Link verifikasi tidak valid atau sudah kadaluarsa.');
        }

        $email = (string) $request->query('email', '');

        if (! $user->backup_email || $user->backup_email !== strtolower($email)) {
            abort(403, 'Email tidak cocok dengan data terbaru.');
        }

        if (! $user->backup_email_verified_at) {
            $user->forceFill(['backup_email_verified_at' => now()])->save();

            activity('auth')
                ->causedBy($user)
                ->withProperties(['backup_email' => $email])
                ->event('backup_email_verified')
                ->log('Backup email verified');
        }

        return redirect()->route('profile.edit')
            ->with('toast', ['type' => 'success', 'title' => 'Verifikasi Berhasil', 'message' => 'Recovery email Anda sudah terverifikasi.']);
    }

    /**
     * Resend verification email.
     */
    public function resend(Request $request): RedirectResponse
    {
        $this->ensureRoleAllowed($request);
        $user = $request->user();

        if (! $user->backup_email) {
            return back()->withErrors(['backup_email' => 'Belum ada recovery email yang ter-set.']);
        }
        if ($user->backup_email_verified_at) {
            return back()->with('toast', ['type' => 'info', 'title' => 'Sudah Verified', 'message' => 'Recovery email sudah terverifikasi.']);
        }

        Notification::route('mail', $user->backup_email)
            ->notify(new VerifyBackupEmail($user->id, $user->backup_email));

        return back()->with('toast', ['type' => 'success', 'title' => 'Verifikasi Dikirim Ulang', 'message' => 'Link verifikasi sudah dikirim ulang ke recovery email Anda.']);
    }

    /**
     * Hapus backup email.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->ensureRoleAllowed($request);
        $user = $request->user();

        $user->forceFill([
            'backup_email' => null,
            'backup_email_verified_at' => null,
        ])->save();

        activity('auth')
            ->causedBy($user)
            ->event('backup_email_removed')
            ->log('Backup email removed');

        return back()->with('toast', ['type' => 'success', 'title' => 'Penghapusan Berhasil', 'message' => 'Recovery email berhasil dihapus.']);
    }
}
