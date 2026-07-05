<x-app-layout>
    <x-slot name="header">System Settings</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">System Settings</h1>
                <p class="text-slate-500 mt-1">Konfigurasi sistem level lanjut. Hanya superadmin yang bisa mengakses.</p>
            </div>
        </div>

        {{-- Email Configuration --}}
        <section class="rounded-2xl bg-white border border-[#eedde2] p-6 mb-6">
            <div class="flex items-start gap-3 mb-5">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-[#fdf2f5] shrink-0">
                    <svg class="w-5 h-5 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M22 4l-10 8L2 4"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Email Configuration</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Domain email untuk akun internal (admin & nailist).</p>
                </div>
            </div>

            <form id="domainForm" method="POST" action="{{ route('dashboard.system-settings.update-email-domain') }}">
                @csrf
                @method('PUT')

                <label for="email_domain" class="block text-sm font-medium text-slate-700 mb-1.5">Domain Email</label>
                <div class="flex rounded-lg border border-[#ece4e8] focus-within:border-[#a23f66] focus-within:ring-1 focus-within:ring-[#a23f66] overflow-hidden max-w-md">
                    <span class="inline-flex items-center px-3 bg-[#fdf2f5] text-sm text-[#a23f66] font-medium">@</span>
                    <input id="email_domain" name="email_domain" type="text" required
                           value="{{ old('email_domain', $emailDomain) }}"
                           placeholder="nailart.com"
                           oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9.-]/g,'')"
                           class="flex-1 border-0 text-sm focus:ring-0 font-mono">
                </div>
                @error('email_domain') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

                <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800">
                    <p class="font-semibold mb-1">⚠ Penting:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        <li>Domain ini hanya berlaku untuk akun <b>BARU</b> yang dibuat setelah ini.</li>
                        <li>Akun yang sudah ada <b>tetap menggunakan domain saat akun dibuat</b>.</li>
                        <li>Pastikan domain yang dimasukkan benar — typo akan membuat akun baru tidak bisa dibuat.</li>
                    </ul>
                </div>

                <div class="flex items-center justify-end gap-2 mt-5">
                    <button type="button" id="saveDomainBtn"
                            class="rounded-lg bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </section>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('saveDomainBtn').addEventListener('click', async () => {
            const newValue = document.getElementById('email_domain').value.trim();
            const currentValue = @json($emailDomain);

            if (newValue === currentValue) {
                window.toast?.('info', 'Tidak Ada Perubahan', `Domain email tetap @${currentValue}.`);
                return;
            }

            const result = await Swal.fire({
                title: 'Ganti Domain Email?',
                html: `Domain baru: <b>@${newValue}</b><br>Domain lama: <b>@${currentValue}</b><br><br>
                       <span class="text-amber-700 text-sm">Akun lama tetap pakai @${currentValue}. Hanya akun baru yang akan pakai @${newValue}.</span>`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Simpan',
                confirmButtonColor: '#a23f66',
                cancelButtonColor: '#94a3b8',
                reverseButtons: true,
            });

            if (result.isConfirmed) {
                document.getElementById('domainForm').submit();
            }
        });
    </script>
</x-app-layout>
