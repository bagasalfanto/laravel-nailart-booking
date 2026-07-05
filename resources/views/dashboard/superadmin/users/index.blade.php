<x-app-layout>
    <x-slot name="header">User Management</x-slot>

    @php
        $isSuperadmin = auth()->user()->hasRole('superadmin');
        $allowedRoles = $isSuperadmin ? ['admin' => 'Admin', 'nailist' => 'Nailist'] : ['nailist' => 'Nailist'];
    @endphp

    <div class="max-w-7xl mx-auto" x-data="userCreateModal()">
        {{-- Header --}}
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">User Management</h1>
                <p class="text-slate-500 mt-1">Kelola seluruh akun pengguna terdaftar.</p>
            </div>
            <div class="flex items-center gap-3">
                <x-export-dropdown
                    csv-url="{{ route('dashboard.users.export', request()->query()) }}"
                    pdf-url="{{ route('dashboard.users.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                />
                <button type="button" @click="open = true"
                        class="inline-flex items-center gap-2 rounded-full bg-[#a23f66] px-4 py-2.5 text-sm font-medium text-white hover:opacity-90 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>
                    Tambah User
                </button>
            </div>
        </div>

        {{-- Stats grid --}}
        @php
            $cards = [
                ['label' => 'Total User',    'value' => $stats['total'],    'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z', 'bg' => 'bg-[#fdf2f5]', 'fg' => 'text-[#a23f66]'],
                ['label' => 'Total Admin',   'value' => $stats['admin'],    'icon' => 'M12 11c2.21 0 4-1.79 4-4S14.21 3 12 3 8 4.79 8 7s1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4z', 'bg' => 'bg-amber-50', 'fg' => 'text-amber-600'],
                ['label' => 'Total Nailist', 'value' => $stats['nailist'],  'icon' => 'M5 3v18m14-18v18M9 7h6m-6 4h6m-6 4h6', 'bg' => 'bg-violet-50', 'fg' => 'text-violet-600'],
                ['label' => 'Total Customer','value' => $stats['customer'], 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'bg' => 'bg-sky-50', 'fg' => 'text-sky-600'],
            ];
        @endphp

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach ($cards as $c)
                <div class="rounded-2xl bg-white border border-[#eedde2] p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 {{ $c['fg'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 truncate">{{ $c['label'] }}</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ number_format($c['value']) }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filter toolbar --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-4">
                    <label class="block text-xs text-slate-500 mb-1">Nama</label>
                    <input type="text" id="f_name" placeholder="Filter nama…"
                           class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs text-slate-500 mb-1">Email</label>
                    <input type="text" id="f_email" placeholder="Filter email…"
                           class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs text-slate-500 mb-1">No. HP</label>
                    <input type="text" id="f_phone" placeholder="Filter no. HP…" inputmode="numeric"
                           oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                           class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs text-slate-500 mb-1">Role</label>
                    <select id="f_role" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs text-slate-500 mb-1">Status</label>
                    <select id="f_status" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        <option value="">Semua Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="button" id="resetFilter"
                            class="w-full rounded-lg border border-[#eedde2] px-3 py-2 text-sm text-slate-600 hover:bg-[#fdf2f5]">Reset</button>
                </div>
            </div>
        </div>

        {{-- Tabel Yajra --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden">
            <div class="overflow-x-auto dt-yajra-wrap">
                <table id="usersTable" class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th>User</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Status</th>
                            <th>Role</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        {{-- Modal: Tambah User --}}
        <div x-show="open" x-cloak x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
             @keydown.escape.window="open = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.outside="open = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-[#f1e4e8]">
                    <h3 class="text-lg font-semibold text-slate-900">Tambah User</h3>
                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="px-6 py-5 space-y-4">
                    <div>
                        <label for="role" class="block text-sm font-medium text-slate-700">Role <span class="text-rose-500">*</span></label>
                        <select id="role" x-model="form.role" required
                                class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            <option value="">Pilih role…</option>
                            @foreach ($allowedRoles as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.role" x-text="errors.role" class="mt-1 text-xs text-rose-600"></p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email <span class="text-rose-500">*</span></label>
                        <div class="mt-1 flex rounded-lg border border-[#ece4e8] focus-within:border-[#a23f66] focus-within:ring-1 focus-within:ring-[#a23f66] overflow-hidden">
                            <input id="email" x-model="form.emailLocal" type="text" required placeholder="username"
                                   class="flex-1 border-0 text-sm focus:ring-0"
                                   oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9._-]/g,'')">
                            <span class="inline-flex items-center px-3 bg-[#fdf2f5] text-sm text-[#a23f66] font-medium">{{ '@'.$emailDomain }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Domain otomatis {{ '@'.$emailDomain }}.</p>
                        <p x-show="errors.email" x-text="errors.email" class="mt-1 text-xs text-rose-600"></p>
                    </div>

                    <div class="rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-700">
                        <p class="font-medium">Catatan:</p>
                        <ul class="mt-1 list-disc list-inside space-y-0.5">
                            <li>Password akan di-generate otomatis (12 karakter)</li>
                            <li>User wajib ganti password & lengkapi profil saat login pertama</li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="open = false" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" :disabled="loading"
                                class="rounded-lg bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90 disabled:opacity-50 inline-flex items-center gap-2">
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                            <span x-text="loading ? 'Membuat…' : 'Buat Akun'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('partials.yajra-assets')

    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* 1. Mencegat DataTables versi 1.x (Select di dalam Label) */
        .dataTables_length label {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            white-space: nowrap !important;
            width: max-content !important; /* Paksa agar tidak menyusut turun */
        }

        /* 2. Mencegat DataTables versi 2.x (Select dan Label bersebelahan) */
        .dt-length {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            white-space: nowrap !important;
            width: max-content !important;
        }
        .dt-length label { margin: 0 !important; }

        /* 3. Paksa ukuran kotak dropdown dan ikon panahnya agar lega */
        .dataTables_length select,
        .dt-length select {
            width: 75px !important;
            min-width: 75px !important;
            padding: 4px 30px 4px 12px !important; /* Padding kanan 30px khusus untuk panah */
            background-position: right 8px center !important; /* Dorong panah ke ujung kanan */
            border-radius: 6px !important;
            box-sizing: border-box !important;
        }
    </style>

    <script>
        // Reusable credentials popup.
        function showCredentialsPopup(data, opts = {}) {
            const title = opts.title || 'Akun Berhasil Dibuat';
            const intro = opts.intro || `Akun <b>${data.role}</b> berhasil dibuat. Simpan kredensial berikut & kirim ke user — <b>password tidak akan ditampilkan lagi</b>.`;

            const html = `
                <div class="text-left text-sm">
                    <p class="text-slate-600 mb-3">${intro}</p>
                    <div class="rounded-lg border border-slate-200 divide-y divide-slate-200 bg-slate-50">
                        <div class="px-3 py-2 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Email</p>
                                <p id="cred-email" class="font-mono text-slate-800 truncate">${data.email}</p>
                            </div>
                            <button type="button" data-copy="cred-email" class="copy-btn shrink-0 rounded-md bg-white border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">Copy</button>
                        </div>
                        <div class="px-3 py-2 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Password</p>
                                <p id="cred-pass" class="font-mono text-slate-800 truncate">${data.password}</p>
                            </div>
                            <button type="button" data-copy="cred-pass" class="copy-btn shrink-0 rounded-md bg-white border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">Copy</button>
                        </div>
                    </div>
                    <button type="button" id="copy-both"
                            class="mt-3 w-full rounded-lg bg-[#a23f66] text-white text-sm font-medium py-2 hover:opacity-90">
                        Copy Email & Password
                    </button>
                </div>
            `;

            Swal.fire({
                title, html,
                showConfirmButton: true,
                confirmButtonText: 'Selesai',
                confirmButtonColor: '#a23f66',
                showCloseButton: true,
                allowOutsideClick: false,
                width: 460,
                didOpen: () => {
                    const flash = (btn, text = 'Copied!') => {
                        const orig = btn.textContent;
                        btn.textContent = text;
                        btn.classList.add('bg-emerald-50', 'border-emerald-300', 'text-emerald-700');
                        setTimeout(() => {
                            btn.textContent = orig;
                            btn.classList.remove('bg-emerald-50', 'border-emerald-300', 'text-emerald-700');
                        }, 1200);
                    };
                    document.querySelectorAll('.copy-btn').forEach(btn => {
                        btn.addEventListener('click', async () => {
                            const target = document.getElementById(btn.dataset.copy);
                            await navigator.clipboard.writeText(target.textContent.trim());
                            flash(btn);
                        });
                    });
                    document.getElementById('copy-both').addEventListener('click', async (e) => {
                        const text = `Email: ${data.email}\nPassword: ${data.password}`;
                        await navigator.clipboard.writeText(text);
                        e.currentTarget.textContent = 'Tersalin ke Clipboard ✓';
                        e.currentTarget.classList.add('bg-emerald-600');
                        setTimeout(() => {
                            e.currentTarget.textContent = 'Copy Email & Password';
                            e.currentTarget.classList.remove('bg-emerald-600');
                        }, 1500);
                    });
                },
                willClose: () => {
                    if (opts.reloadOnClose !== false && window.usersTable) {
                        window.usersTable.ajax.reload(null, false);
                    }
                },
            });
        }

        function userCreateModal() {
            const regenerateUrl = (id) =>
                @json(url('dashboard/users')) + '/' + id + '/regenerate-password';

            return {
                open: false,
                loading: false,
                form: { role: '', emailLocal: '' },
                errors: {},

                async submit() {
                    this.errors = {};
                    this.loading = true;
                    const email = (this.form.emailLocal || '').trim() + '@' + @json($emailDomain);
                    try {
                        const res = await fetch(@json(route('dashboard.users.store')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ role: this.form.role, email }),
                        });
                        const json = await res.json();
                        if (!res.ok) {
                            if (res.status === 422 && json.errors) {
                                Object.entries(json.errors).forEach(([k, v]) => { this.errors[k] = v[0]; });
                            } else {
                                window.toast('error', 'Gagal', json.message || 'Terjadi kesalahan.');
                            }
                            return;
                        }
                        this.open = false;
                        this.form = { role: '', emailLocal: '' };
                        window.location.reload();
                    } catch (e) {
                        window.toast('error', 'Network Error', e.message);
                    } finally {
                        this.loading = false;
                    }
                },
            }
        }

        // Helper: konfirmasi & eksekusi regenerate password (di-call dari handler di luar Alpine).
        async function confirmRegenerate(userId, name, email) {
            const result = await Swal.fire({
                title: 'Regenerate Password?',
                html: `Password untuk <b>${name}</b> (${email}) akan diganti dengan password baru.<br><br>
                       <span class="text-rose-600 text-sm">Password lama tidak akan bisa dipakai lagi & user akan otomatis logout.</span>`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Regenerate',
                confirmButtonColor: '#a23f66',
                cancelButtonColor: '#94a3b8',
                reverseButtons: true,
            });
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Memproses…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const url = @json(url('dashboard/users')) + '/' + userId + '/regenerate-password';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                window.location.reload();
            } catch (e) {
                window.toast('error', 'Network Error', e.message);
            }
        }

        $(function () {
            const table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [],
                ajax: {
                    url: @json(route('dashboard.users.data')),
                    data: function (d) {
                        d.f_name   = $('#f_name').val();
                        d.f_email  = $('#f_email').val();
                        d.f_phone  = $('#f_phone').val();
                        d.f_role   = $('#f_role').val();
                        d.f_status = $('#f_status').val();
                    },
                    error: function (xhr) {
                        const table = $(this).DataTable();
                        table.processing(false);
                        if (xhr.status === 401) { window.location.reload(); return; }
                        window.toast?.('error', 'Gagal Memuat', 'Server error — coba refresh halaman.');
                    },
                },
                columns: [
                    { data: 'user_label',   name: 'full_name', className: 'whitespace-nowrap' },
                    { data: 'email',        name: 'email' },
                    { data: 'phone_label',  name: 'phone_number' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'role_badges',  name: 'roles', orderable: false, searchable: false },
                    { data: 'actions',      name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' },
                ],
                language: {
                    search: 'Cari global:',
                    lengthMenu: 'Tampilkan _MENU_ entri',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ entri',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total)',
                    zeroRecords: 'Tidak ada user yang cocok dengan filter.',
                    processing: '<span class="text-slate-500">Memuat…</span>',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });
            window.usersTable = table;

            let timer;
            const reloadDebounced = () => { clearTimeout(timer); timer = setTimeout(() => table.ajax.reload(null, false), 300); };

            $('#f_name, #f_email, #f_phone').on('keyup', reloadDebounced);
            $('#f_role, #f_status').on('change', () => table.ajax.reload(null, false));
            $('#resetFilter').on('click', () => {
                $('#f_name, #f_email, #f_phone').val('');
                $('#f_role, #f_status').val('');
                table.ajax.reload(null, false);
            });

            // Wire tombol Reset PW di setiap row (event delegation).
            $('#usersTable tbody').on('click', '[data-regen-id]', function () {
                confirmRegenerate(this.dataset.regenId, this.dataset.regenName, this.dataset.regenEmail);
            });

            // Wire tombol Hapus per row.
            $('#usersTable tbody').on('click', '[data-del-id]', function () {
                confirmDeleteUser(this.dataset.delId, this.dataset.delName, this.dataset.delEmail, this.dataset.delRole);
            });
        });

        async function confirmDeleteUser(userId, name, email, role) {
            const result = await Swal.fire({
                title: `Hapus akun ${role}?`,
                html: `
                    <p style="text-align:left; color:#475569; font-size:0.875rem; line-height:1.5;">
                        Akun <strong>${name}</strong> (${email}) akan dihapus.<br>
                        Riwayat booking, review, dan portfolio yang berkaitan tetap tersimpan.<br>
                        Email akan di-release sehingga bisa dipakai untuk daftar akun baru.
                    </p>
                    <p style="text-align:left; color:#e11d48; font-size:0.8125rem; margin-top:0.75rem;">
                        Tindakan ini tidak bisa dibatalkan.
                    </p>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus akun',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#94a3b8',
                reverseButtons: true,
                focusCancel: true,
            });

            if (! result.isConfirmed) return;

            try {
                const res = await fetch(`{{ url('dashboard/users') }}/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json().catch(() => ({}));

                if (! res.ok || ! data.success) {
                    window.toast('error', 'Gagal Menghapus', data.message || 'Terjadi kesalahan saat menghapus akun.');
                    return;
                }

                window.toast('success', 'Akun Dihapus', data.message || `Akun ${email} berhasil dihapus.`);
                if (window.usersTable) window.usersTable.ajax.reload(null, false);
            } catch (err) {
                window.toast('error', 'Gagal Menghapus', err.message || 'Network error.');
            }
        }
        @if (session('credentials'))
            document.addEventListener('DOMContentLoaded', () => {
                showCredentialsPopup(@json(session('credentials')));
            });
        @endif
    </script>
</x-app-layout>
