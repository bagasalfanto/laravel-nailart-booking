<x-guest-layout>
    <div class="min-h-screen bg-[#f5edee] flex items-center justify-center py-10 px-4">
        <div class="w-full max-w-2xl bg-white rounded-2xl border border-[#eedde2] shadow-sm">
            <div class="px-8 pt-8 pb-4 border-b border-[#f1e4e8]">
                @if ($mode === 'full')
                    <p class="text-xs font-semibold tracking-widest text-[#a23f66] uppercase">Activate Account</p>
                    <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 mt-2">Lengkapi Profil Anda</h1>
                    <p class="text-sm text-slate-500 mt-1">Akun dibuat oleh admin. Silakan ganti password dan lengkapi data berikut sebelum melanjutkan.</p>
                @else
                    <p class="text-xs font-semibold tracking-widest text-[#a23f66] uppercase">Reset Password</p>
                    <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 mt-2">Ganti Password Anda</h1>
                    <p class="text-sm text-slate-500 mt-1">Password Anda telah di-reset oleh admin. Silakan buat password baru sebelum melanjutkan.</p>
                @endif
            </div>

            <form method="POST" action="{{ route('account.setup.update') }}" class="px-8 py-6 space-y-5">
                @csrf

                <div>
                    <label class="block text-xs uppercase tracking-wide text-slate-500">Email</label>
                    <p class="mt-1 text-slate-800">{{ $user->email }}</p>
                </div>

                @if ($mode === 'full')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-slate-700">Full Name <span class="text-rose-500">*</span></label>
                            <input id="full_name" name="full_name" type="text" required value="{{ old('full_name') }}"
                                   class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            @error('full_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-medium text-slate-700">Username <span class="text-rose-500">*</span></label>
                            <input id="username" name="username" type="text" required value="{{ old('username') }}"
                                   class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]"
                                   oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9_.]/g,'')">
                            @error('username') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-slate-700">No. HP <span class="text-rose-500">*</span></label>
                        <input id="phone_number" name="phone_number" type="text" required value="{{ old('phone_number') }}"
                               inputmode="numeric" pattern="[0-9]*" maxlength="20" placeholder="08888888888"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                               onkeypress="return /[0-9]/.test(event.key)"
                               class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        @error('phone_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="{{ $mode === 'full' ? 'border-t border-[#f1e4e8] pt-5' : '' }}">
                    <h2 class="text-sm font-semibold text-slate-700 mb-3">Ganti Password</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700">Password Baru <span class="text-rose-500">*</span></label>
                            <input id="password" name="password" type="password" required
                                   class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Password <span class="text-rose-500">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                   class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4">
                    <button type="button"
                            onclick="document.getElementById('logout-from-setup').submit()"
                            class="text-sm text-slate-500 hover:text-rose-600">
                        Keluar
                    </button>
                    <button type="submit" class="rounded-full bg-[#a23f66] px-6 py-2.5 text-sm font-medium text-white hover:opacity-90 shadow-sm">
                        Simpan & Lanjutkan
                    </button>
                </div>
            </form>

            <form id="logout-from-setup" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>
        </div>
    </div>
</x-guest-layout>
