<?php

namespace App\Http\Controllers\Dashboard\Superadmin;

use App\Http\Controllers\Controller;
use App\Support\SystemSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        return view('dashboard.superadmin.system-settings.index', [
            'emailDomain' => SystemSetting::emailDomain(),
        ]);
    }

    public function updateEmailDomain(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Domain valid: huruf/angka/dash, minimal 1 dot, no @, lowercase di-handle.
            'email_domain' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9.-]+\.[a-z]{2,}$/i'],
        ], [
            'email_domain.regex' => 'Format domain tidak valid (contoh yang benar: nailart.com).',
        ]);

        $newDomain = strtolower(trim($validated['email_domain']));
        SystemSetting::set('email_domain', $newDomain);

        activity('system')
            ->causedBy($request->user())
            ->withProperties(['email_domain' => $newDomain])
            ->event('email_domain_changed')
            ->log("Email domain changed to {$newDomain}");

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Domain Diperbarui',
            'message' => "Domain email baru: @{$newDomain}. Akun lama tetap pakai domain sebelumnya.",
        ]);
    }
}
