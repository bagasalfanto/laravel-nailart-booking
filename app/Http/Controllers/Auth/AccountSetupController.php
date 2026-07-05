<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountSetupController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->must_complete_profile && ! $user->must_change_password) {
            return redirect()->route($user->homeRoute());
        }

        return view('auth.account-setup', [
            'user' => $user,
            'mode' => $user->must_complete_profile ? 'full' : 'password-only',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->must_complete_profile && ! $user->must_change_password) {
            return redirect()->route($user->homeRoute());
        }

        // Bersihkan phone digit-only (hanya relevan di mode full).
        if ($user->must_complete_profile && $request->filled('phone_number')) {
            $request->merge([
                'phone_number' => preg_replace('/[^0-9]/', '', (string) $request->input('phone_number')),
            ]);
        }

        $rules = [
            'password' => ['required', 'string', 'confirmed', 'max:72', Password::defaults()],
        ];

        if ($user->must_complete_profile) {
            $rules = array_merge($rules, [
                'full_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z .\-]+$/'],
                'username' => ['required', 'string', 'min:3', 'max:32', 'regex:/^[a-z0-9_.]+$/i', Rule::unique(User::class)->ignoreModel($user)],
                'phone_number' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/', Rule::unique(User::class)->ignoreModel($user)],
            ]);
        }

        $validated = $request->validate($rules, [
            'full_name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, dan strip.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, dan underscore.',
            'username.unique' => 'Username sudah digunakan.',
            'phone_number.regex' => 'Nomor telepon hanya boleh berisi angka.',
            'phone_number.unique' => 'Nomor telepon sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $payload = [
            'password' => Hash::make($validated['password']),
            'must_complete_profile' => false,
            'must_change_password' => false,
        ];

        if ($user->must_complete_profile) {
            $payload['full_name'] = trim($validated['full_name']);
            $payload['username'] = strtolower($validated['username']);
            $payload['phone_number'] = $validated['phone_number'];
        }

        $user->update($payload);

        Auth::logoutOtherDevices($validated['password']);

        return redirect()
            ->route($user->homeRoute())
            ->with('toast', ['type' => 'success', 'title' => 'Selamat Datang', 'message' => 'Akun Anda berhasil diperbarui.']);
    }
}
