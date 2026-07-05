<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProfileController extends Controller
{
    private function deletePublicAvatar(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalizedPath = str_replace('\\', '/', $path);
        $filename = basename($normalizedPath);

        if (
            $filename === ''
            || $filename === '.'
            || $filename === '..'
            || $normalizedPath !== 'avatars/'.$filename
        ) {
            return;
        }

        Storage::disk('public')->delete('avatars/'.$filename);
    }

    public function edit(Request $request): View
    {
        $user = $request->user();
        $isNailist = $user->hasRole('nailist');

        return view('profile.edit', [
            'user'              => $user,
            'isNailist'         => $isNailist,
            'specialtyOptions'  => $isNailist ? Specialty::active()->orderBy('name')->get() : collect(),
            'selectedSpecialty' => $isNailist
                ? ($user->nailist?->specialties()->pluck('specialties.id')->all() ?? [])
                : [],
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Email sengaja TIDAK ada di payload — read-only di profile, dikelola dari User Management.
        $userPayload = [
            'full_name'    => $validated['full_name'],
            'username'     => $validated['username'],
            'phone_number' => $validated['phone_number'] ?? null,
        ];

        if ($request->boolean('remove_avatar') && $user->avatar) {
            $this->deletePublicAvatar($user->avatar);
            $userPayload['avatar'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                $this->deletePublicAvatar($user->avatar);
            }

            $userPayload['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($userPayload)->save();

        if ($user->hasRole('nailist') && $user->nailist) {
            $user->nailist()->update([
                'title' => $validated['title'] ?? null,
                'bio'   => $validated['bio'] ?? null,
            ]);

            $user->nailist->specialties()->sync($validated['specialties'] ?? []);
        }

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated')
            ->with('toast', ['type' => 'success', 'title' => 'Profil Diperbarui', 'message' => 'Data profil Anda berhasil disimpan.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Hanya customer yang boleh self-delete via halaman profile.
        // Admin/superadmin/nailist harus dihapus oleh staff lain via User Management.
        if (! $user->hasRole('customer')) {
            throw new AccessDeniedHttpException('Penghapusan akun via profile hanya tersedia untuk customer.');
        }

        // Google OAuth users don't have a password — skip password confirmation
        if (! $user->google_id) {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);
        }

        DB::transaction(function () use ($user, $request) {
            // 1. Hapus avatar dari storage agar tidak nyangkut.
            if ($user->avatar) {
                $this->deletePublicAvatar($user->avatar);
            }

            // 2. Scramble identitas — agar email/username/phone bisa dipakai untuk register baru,
            //    dan user soft-deleted tidak bisa login lagi (auth fail karena email sudah ditukar).
            $deletedToken = (string) Str::uuid();
            $user->forceFill([
                'email'        => 'deleted-'.$deletedToken.'@deleted.local',
                'username'     => 'deleted-'.$deletedToken,
                'phone_number' => null,
                'google_id'    => null,
                'avatar'       => null,
                'password'     => Hash::make(Str::random(40)),
                'status'       => 'inactive',
            ])->save();

            // 3. Force logout di semua device lain.
            try {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            } catch (\Throwable $e) {
                // session driver mungkin bukan DB — abaikan.
            }

            // 4. Audit log.
            activity('auth')
                ->causedBy($user)
                ->withProperties(['deleted_token' => $deletedToken])
                ->event('account_self_deleted')
                ->log('Customer self-deleted account (soft delete)');

            // 5. Soft delete (set deleted_at).
            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Akun Dihapus',
                'message' => 'Akun kamu sudah dihapus. Riwayat booking & review tetap terjaga.',
            ]);
    }
}
