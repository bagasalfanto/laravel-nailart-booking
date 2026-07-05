@php
    $user = auth()->user();
    $hasBackup = filled($user->backup_email);
    $verified  = $user->backup_email_verified_at !== null;
@endphp

<section class="rounded-2xl bg-white border border-[#eedde2] p-6">
    <div class="mb-5">
        <h2 class="text-lg font-semibold text-slate-900">Recovery Email</h2>
        <p class="text-sm text-slate-500 mt-1">
            Email cadangan untuk reset password jika Anda lupa & email primary tidak bisa diakses.
            Link reset akan dikirim ke alamat ini.
        </p>
    </div>

    @if ($hasBackup)
        <div class="rounded-xl border border-[#f1e4e8] p-4 mb-4">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Recovery Email</p>
                    <p class="font-mono text-slate-800 truncate">{{ $user->backup_email }}</p>
                </div>
                @if ($verified)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Verified · {{ $user->backup_email_verified_at->format('d M Y') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4M12 16h.01"/></svg>
                        Belum Verified
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2 mt-4 flex-wrap">
                @unless ($verified)
                    <form method="POST" action="{{ route('account.backup-email.resend') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-[#a23f66] px-3 py-1.5 text-xs font-medium text-[#a23f66] hover:bg-[#fdf2f5]">
                            Kirim Ulang Link Verifikasi
                        </button>
                    </form>
                @endunless
                <button type="button" onclick="document.getElementById('changeBackupForm').classList.toggle('hidden')"
                        class="rounded-lg border border-[#eedde2] px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-[#fdf2f5]">
                    Ganti Recovery Email
                </button>
                <form method="POST" action="{{ route('account.backup-email.destroy') }}"
                      onsubmit="return confirm('Hapus recovery email? Anda tidak bisa reset password via email cadangan setelah ini.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-100">
                        Hapus
                    </button>
                </form>
            </div>
        </div>

        {{-- Form ganti — hidden by default --}}
        <form method="POST" action="{{ route('account.backup-email.store') }}" id="changeBackupForm" class="hidden">
            @csrf
            <label class="block text-sm font-medium text-slate-700 mb-1">Recovery email baru</label>
            <div class="flex gap-2">
                <input type="email" name="backup_email" required placeholder="email-pribadi@example.com"
                       class="flex-1 rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                <button type="submit" class="rounded-lg bg-[#a23f66] px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                    Simpan & Kirim Verifikasi
                </button>
            </div>
            @error('backup_email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </form>
    @else
        {{-- Belum ada backup email --}}
        <form method="POST" action="{{ route('account.backup-email.store') }}">
            @csrf
            <label for="backup_email" class="block text-sm font-medium text-slate-700 mb-1">Email cadangan</label>
            <div class="flex gap-2">
                <input id="backup_email" type="email" name="backup_email" required placeholder="email-pribadi@example.com"
                       value="{{ old('backup_email') }}"
                       class="flex-1 rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                <button type="submit" class="rounded-lg bg-[#a23f66] px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                    Set & Verifikasi
                </button>
            </div>
            <p class="text-xs text-slate-500 mt-2">Anda akan menerima link verifikasi di email tersebut (link aktif 1 jam).</p>
            @error('backup_email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </form>
    @endif
</section>
