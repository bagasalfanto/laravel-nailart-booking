<section class="space-y-6 rounded-2xl border border-rose-100 bg-rose-50/40 p-6">
    <header>
        <h2 class="text-xl font-semibold text-rose-700">Hapus Akun</h2>
        <p class="mt-1 text-sm text-slate-600">
            Akun kamu akan dihapus dari sistem. Untuk menjaga integritas data,
            <strong>riwayat booking & review tetap tersimpan</strong> dengan nama asli kamu.
            Email kamu akan di-release sehingga bisa dipakai untuk daftar akun baru. Aksi ini tidak bisa dibatalkan.
        </p>
    </header>

    {{-- Form tersembunyi yang di-submit oleh JS setelah konfirmasi SweetAlert2. --}}
    <form id="deleteAccountForm" method="POST" action="{{ route('profile.destroy') }}" class="hidden">
        @csrf
        @method('DELETE')
        <input type="hidden" name="password" id="deleteAccountPassword">
    </form>

    <button
        type="button"
        id="deleteAccountTrigger"
        class="inline-flex items-center justify-center rounded-full bg-rose-600 px-5 py-2 text-sm font-semibold text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2 transition"
    >
        <p class="text-slate-600 text-white">Hapus Akun Saya</p>
    </button>

    {{-- Tampilkan error password (kalau redirect-back dari controller dengan error). --}}
    @if ($errors->userDeletion->any())
        <p class="text-sm text-rose-600">{{ $errors->userDeletion->first() }}</p>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const trigger = document.getElementById('deleteAccountTrigger');
            const form    = document.getElementById('deleteAccountForm');
            const pwdInput= document.getElementById('deleteAccountPassword');
            const isGoogle = @json(!empty($user->google_id));

            if (!trigger || !form || !pwdInput) return;

            trigger.addEventListener('click', async () => {
                if (typeof Swal === 'undefined') {
                    alert('SweetAlert2 belum dimuat — refresh halaman dan coba lagi.');
                    return;
                }

                // Google OAuth users — no password, just confirmation
                if (isGoogle) {
                    const result = await Swal.fire({
                        title: 'Hapus akun kamu?',
                        html: `
                            <p style="text-align:left; color:#475569; font-size:0.875rem; line-height:1.5;">
                                Akun Google kamu akan dihapus permanen. Riwayat booking & review masih tersimpan, tapi kamu tidak bisa login lagi.
                            </p>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus akun saya',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#94a3b8',
                        reverseButtons: true,
                        focusCancel: true,
                    });

                    if (result.isConfirmed) {
                        form.submit();
                    }
                    return;
                }

                // Regular users — require password
                const { value: password, isConfirmed } = await Swal.fire({
                    title: 'Hapus akun kamu?',
                    html: `
                        <p style="text-align:left; color:#475569; font-size:0.875rem; line-height:1.5; margin-bottom:0.75rem;">
                            Akun ini akan dihapus permanen. Riwayat booking & review masih tersimpan, tapi kamu tidak bisa login lagi.
                        </p>
                        <p style="text-align:left; color:#475569; font-size:0.875rem; margin-bottom:0.5rem;">
                            Masukkan password kamu untuk konfirmasi:
                        </p>
                    `,
                    icon: 'warning',
                    input: 'password',
                    inputPlaceholder: 'Password kamu',
                    inputAttributes: {
                        autocapitalize: 'off',
                        autocomplete: 'current-password',
                        maxlength: '72',
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus akun saya',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#94a3b8',
                    reverseButtons: true,
                    focusCancel: true,
                    preConfirm: (value) => {
                        if (!value) {
                            Swal.showValidationMessage('Password wajib diisi.');
                            return false;
                        }
                        return value;
                    },
                });

                if (isConfirmed && password) {
                    pwdInput.value = password;
                    form.submit();
                }
            });
        });
    </script>
</section>
